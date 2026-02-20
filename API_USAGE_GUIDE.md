# API Usage Guide / دليل استخدام الـ API

This document explains how to use the API for **Publishing Posts**, **Sessions (Consultations)**, and **Diets**.
هذا المستند يشرح كيفية استخدام الـ API لـ **نشر المنشورات**، **الحصص (الاستشارات)**، و **الحمية الغذائية**.

---

## 1. Publishing a Post / نشر منشور

### Endpoint / الرابط
`POST /api/forums/{forumId}/posts`

### Description / الوصف
Create a new post in a specific forum.
إنشاء منشور جديد في منتدى محدد.

### Headers / الترويسات
- `Authorization`: `Bearer {token}`
- `Content-Type`: `application/json`
- `Accept`: `application/json`

### Parameters (Body) / المعاملات
| Field | Type | Required | Description | الوصف |
|-------|------|----------|-------------|-------|
| `title` | string | Yes | The title of the post | عنوان المنشور |
| `content`| string | Yes | The content of the post | محتوى المنشور |

### Example Request / مثال للطلب
```json
{
    "title": "My Healthy Journey",
    "content": "I started my diet today and I feel great!"
}
```

### Example Response / مثال للاستجابة
```json
{
    "id": 1,
    "forum_id": 5,
    "user_id": 10,
    "title": "My Healthy Journey",
    "content": "I started my diet today..."
}
```

---

## 2. Sessions (Consultations) / الحصص (الاستشارات)

*Note: In this system, "Sessions" are interpreted as "Consultations" with a doctor.*
*ملاحظة: في هذا النظام، تم تفسير "الحصص" على أنها "استشارات" مع الطبيب.*

### A. Request a Session / طلب حصة (استشارة)
**Endpoint**: `POST /api/consultations`

### Description / الوصف
Schedule a new consultation session with a doctor.
حجز موعد جديد لاستشارة مع طبيب.

### Parameters (Body) / المعاملات
| Field | Type | Required | Description | الوصف |
|-------|------|----------|-------------|-------|
| `doctor_id` | integer | Yes | ID of the doctor | معرف الطبيب |
| `consultation_type` | string | Yes | `initial`, `follow_up`, or `review` | نوع الاستشارة (أولية، متابعة، مراجعة) |
| `scheduled_date` | date | Yes | Date/Time (e.g., `2024-12-25 14:30:00`) | تاريخ ووقت الاستشارة |
| `notes` | string | No | Optional notes | ملاحظات إضافية |

### Example Request / مثال للطلب
```json
{
    "doctor_id": 3,
    "consultation_type": "initial",
    "scheduled_date": "2024-12-25 10:00:00",
    "notes": "I need help with my keto diet."
}
```

### B. List My Sessions / عرض حصصي (استشاراتي)
**Endpoint**: `GET /api/consultations`

### Description / الوصف
Get a list of all your consultations.
عرض قائمة بجميع استشاراتك.

---

## 3. Diet / الحمية

### A. Get My Current Diet / عرض حميتي الحالية
**Endpoint**: `GET /api/my-diet`

### Description / الوصف
Retrieve the currently active diet plan for the logged-in user.
استرجاع خطة الحمية النشطة حالياً للمستخدم المسجل.

### Response / الاستجابة
Returns the diet details including the doctor, components (meals), and notes.
تعيد تفاصيل الحمية بما في ذلك الطبيب، المكونات (الوجبات)، والملاحظات.

### B. Get My Diet Meals / عرض وجبات حميتي
**Endpoint**: `GET /api/my-diet/meals`

### Description / الوصف
Get specifically the meals/components of the active diet.
عرض الوجبات/المكونات الخاصة بالحمية النشطة.

### C. Create/Assign Diet Plan (For Doctors) / إنشاء/تعيين خطة حمية (للأطباء)
**Endpoint**: `POST /api/diet-plans`

### Parameters (Body) / المعاملات
| Field | Type | Required | Description | الوصف |
|-------|------|----------|-------------|-------|
| `patient_id` | integer | Yes | ID of the patient | معرف المريض |
| `doctor_id` | integer | Yes | ID of the doctor | معرف الطبيب |
| `title` | string | Yes | Title of the plan | عنوان الخطة |
| `daily_calories` | integer | Yes | Target daily calories | السعرات الحرارية اليومية |
| `duration_days` | integer | Yes | Duration in days | المدة بالأيام |
| `start_date` | date | Yes | Start date | تاريخ البدء |
| `end_date` | date | Yes | End date | تاريخ الانتهاء |
| `meals` | array | No | Array of meal objects | قائمة الوجبات |

### Example Request / مثال للطلب
```json
{
    "patient_id": 5,
    "doctor_id": 1,
    "title": "Weight Loss Plan",
    "daily_calories": 2000,
    "duration_days": 30,
    "start_date": "2024-01-01",
    "end_date": "2024-01-31",
    "meals": [
        {
            "day_number": 1,
            "meal_type": "breakfast",
            "meal_name": "Oatmeal",
            "calories": 300
        }
    ]
}
```
