import os
import pickle
import numpy as np
import joblib
from functools import wraps
from flask import Flask, request, jsonify
from flask_cors import CORS
from werkzeug.security import generate_password_hash, check_password_hash
import jwt
import torch
from torchvision import transforms, models
import torch.nn as nn
from datetime import datetime, timedelta
from PIL import Image

# --- قاعدة البيانات ---
from models import db, User, Patient, PatientResult, ECGImage

# ---------- إعداد Flask ----------
app = Flask(__name__)
CORS(app)
app.config['SECRET_KEY'] = "supersecret_long_key_for_security_32_chars_plus"
app.config['SQLALCHEMY_DATABASE_URI'] = 'sqlite:///app.db'
app.config['SQLALCHEMY_TRACK_MODIFICATIONS'] = False
db.init_app(app)

with app.app_context():
    db.create_all()

# ---------- مسارات الموديلات ----------
MODELS_DIR = os.path.join(os.path.dirname(__file__), "models")
IMG_MODEL_PATH = os.path.join(MODELS_DIR, "heart_img.pth") 
TAB_MODEL_PATH = os.path.join(MODELS_DIR, "heart_test.pkl")
SYMPTOMS_MODEL_PATH = os.path.join(MODELS_DIR, "symptoms_model.pkl")

# ---------- JWT ----------
def token_required(f):
    @wraps(f)
    def decorated(*args, **kwargs):
        auth_header = request.headers.get('Authorization')

        if not auth_header:
            return jsonify({"message": "Token missing"}), 401

        try:
            parts = auth_header.split(" ")

            if len(parts) != 2 or parts[0] != "Bearer":
                return jsonify({"message": "Invalid token format"}), 401

            token = parts[1]

            data = jwt.decode(
                token,
                app.config['SECRET_KEY'],
                algorithms=["HS256"]
            )

            user = db.session.get(User, data['user_id'])

            if not user:
                return jsonify({"message": "User not found"}), 401

        except jwt.ExpiredSignatureError:
            return jsonify({"message": "Token expired"}), 401

        except jwt.InvalidTokenError:
            return jsonify({"message": "Invalid token"}), 401

        return f(user, *args, **kwargs)

    return decorated

# ---------- الجهاز ----------
DEVICE = torch.device("cuda" if torch.cuda.is_available() else "cpu")

# ---------- تحميل الموديلات ----------
def smart_load_model(path):
    try:
        return joblib.load(path)
    except:
        try:
            with open(path, 'rb') as f:
                return pickle.load(f)
        except:
            return torch.load(path, map_location=DEVICE)

# 1. موديل الصور
img_model = models.resnet50(weights=None)
num_ftrs = img_model.fc.in_features
img_model.fc = nn.Linear(num_ftrs, 2)
img_model.load_state_dict(torch.load(IMG_MODEL_PATH, map_location=DEVICE))
img_model = img_model.to(DEVICE)
img_model.eval()

# 2 & 3. الموديلات الأخرى
tab_model = smart_load_model(TAB_MODEL_PATH)
symptoms_model = smart_load_model(SYMPTOMS_MODEL_PATH)

transform = transforms.Compose([
    transforms.Resize((224,224)),
    transforms.ToTensor(),
    transforms.Normalize(mean=[0.485,0.456,0.406], std=[0.229,0.224,0.225])
])

# ---------- التسجيل ----------
@app.route('/auth/register', methods=['POST'])
def register():
    data = request.get_json()
    if User.query.filter_by(email=data['email']).first():
        return jsonify({"message": "Email exists"}), 400
    user = User(email=data['email'], password_hash=generate_password_hash(data['password']))
    db.session.add(user)
    db.session.commit()
    return jsonify({"message": "User created"})

@app.route('/auth/login', methods=['POST'])
def login():
    data = request.get_json()

    # التحقق من وجود البيانات
    if not data or not data.get('email') or not data.get('password'):
        return jsonify({"message": "Email and password required"}), 400

    # البحث عن المستخدم
    user = User.query.filter_by(email=data['email']).first()

    # التحقق من كلمة المرور
    if not user or not check_password_hash(user.password_hash, data['password']):
        return jsonify({"message": "Invalid login"}), 401

    # إنشاء التوكن مع وقت انتهاء 24 ساعة
    token = jwt.encode({
        "user_id": user.id,
        "exp": datetime.utcnow() + timedelta(hours=24)  # ⏰ 24 ساعة
    }, app.config['SECRET_KEY'], algorithm="HS256")

    # مهم: بعض الإصدارات ترجع bytes
    if isinstance(token, bytes):
        token = token.decode('utf-8')

    return jsonify({
        "token": token,
        "user": {
            "id": user.id,
            "email": user.email
        }
    }), 200

@app.route('/auth/patients', methods=['POST'])
@token_required
def add_patient(current_user):
    data = request.get_json()
    patient = Patient(owner_id=current_user.id, first_name=data.get("first_name"), 
                      last_name=data.get("last_name"), gender=data.get("gender"))
    db.session.add(patient)
    db.session.commit()
    return jsonify({"patient_id": patient.id})

# ---------- التنبؤ النهائي ----------
@app.route("/predict-ecg-image", methods=["POST"])
@token_required
def predict_ecg_image(current_user):
    if "file" not in request.files:
        return jsonify({"message": "No file uploaded"}), 400
    file = request.files["file"]

    # --- موديل الصورة ---
    image = Image.open(file).convert("RGB")
    image_tensor = transform(image).unsqueeze(0).to(DEVICE)
    with torch.no_grad():
        output = img_model(image_tensor)
        probs = torch.softmax(output, dim=1)
        img_prob = probs[0][0].item()

    # --- البيانات ---
    try:
        age = float(request.form.get("age", 0))
        gender = int(request.form.get("gender", 0))
        troponin = float(request.form.get("troponin", 0))
        ckmb = float(request.form.get("ckmb", 0))
        has_previous = int(request.form.get("has_previous_heart_attack", 0))
        chest_pain = int(request.form.get("chest_pain", 0))
        shortness_of_breath = int(request.form.get("shortness_of_breath", 0))
    except Exception:
        return jsonify({"message": "Invalid input data"}), 400

    # --- مودل الفحوصات ---
    tab_input = np.array([[age, gender, troponin, ckmb]])
    tab_prob = tab_model.predict_proba(tab_input)[0][1]

    # --- مودل الأعراض ---
    symp_input = np.array([[chest_pain, shortness_of_breath, age, gender, has_previous, 0]])
    symp_prob = symptoms_model.predict_proba(symp_input)[0][1]

    # --- دمج الموديلات حسب الجلطة السابقة ---
    data_score = (tab_prob + symp_prob) / 2
    if has_previous == 1:
        final_score = (data_score * 0.9) + (img_prob * 0.1)
        logic = "90% Data / 10% Image"
    else:
        final_score = (img_prob * 0.8) + (data_score * 0.2)
        logic = "80% Image / 20% Data"

    diagnosis = "مصاب بجلطة قلبية" if final_score >= 0.5 else "سليم"

    return jsonify({
        "diagnosis": diagnosis,
        "confidence": round(final_score, 3),
        "details": {
            "image_model_score": round(img_prob, 3),
            "tabular_model_score": round(tab_prob, 3),
            "symptoms_model_score": round(symp_prob, 3),
            "logic": logic
        }
    })

if __name__ == "__main__":
    app.run(host="0.0.0.0", port=8000, debug=True)