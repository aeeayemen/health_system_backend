# Portions (Consultations) API / الحصص (الاستشارات)

في هذا النظام، تم تفسير "الحصص" على أنها **استشارات** (Consultations) بين المريض والطبيب.

## الروابط (Endpoints)

### 1. عرض جميع المشاورات/الحصص
- **الرابط:** `GET /api/consultations`
- **الوصف:** يعرض قائمة بجميع الحصص الخاصة بالمستخدم (سواء كان طبيباً أو مريضاً).
- **التوثيق:** يقوم النظام تلقائياً بفلترة البيانات بناءً على المستخدم المسجل.

### 2. حجز حصة جديدة (للمريض)
- **الرابط:** `POST /api/consultations`
- **الجسم (Body):**
```json
{
    "doctor_id": 1,
    "consultation_type": "initial", 
    "scheduled_date": "2026-03-01 10:00:00",
    "notes": "ملاحظات إضافية"
}
```
- **القيم المتاحة لـ `consultation_type`:** `initial`, `follow_up`, `review`.

### 3. تحديث حالة الحصة (للطبيب أو المسؤول)
- **الرابط:** `PUT /api/consultations/{id}`
- **الجسم (Body):**
```json
{
    "status": "completed",
    "notes": "تمت الجلسة بنجاح",
    "recommendations": "يرجى الالتزام بالحمية",
    "whatsapp_link": "https://wa.me/..."
}
```
- **القيم المتاحة لـ `status`:** `pending`, `completed`, `cancelled`.

### 4. عرض حصة محددة
- **الرابط:** `GET /api/consultations/{id}`

---

> [!TIP]
> جميع هذه العمليات تتطلب تسجيل الدخول (Bearer Token).
