# Developer API Guide - Clinic & Nutrition New Features

This document provides technical details for the mobile/web developers to integrate the new Doctor Clinic features.

## 1. Nutrition Calculations (Specialized)
Calculates BMR, TDEE, TEF, and Macronutrients in one request.

- **Endpoint:** `POST /api/calculations/nutrition`
- **Authentication:** `Bearer {token}`
- **Request Body:**
```json
{
    "weight": 85.0,        // kg
    "height": 180.0,       // cm
    "age": 30,             // years
    "gender": "male",      // "male" | "female"
    "activity_level": "moderate", // "sedentary" | "low" | "moderate" | "active" | "very_active"
    "goal": "lose",        // "maintain" | "lose" (-500) | "gain" (+500)
    "save": true            // boolean (optional) - saves to patient history
}
```
- **Response Example:**
```json
{
    "bmi": 26.23,
    "bmr": 1891.25,
    "total_calories": 2720,
    "macros": {
        "carbs_g": 340.0,
        "protein_g": 136.0,
        "fat_g": 90.7
    },
    "inputs": { ... }
}
```

---

## 2. Nutrition Manuals & References
Provides doctors with clinical manuals and external resources (e.g., Kraus).

- **Endpoint:** `GET /api/references/nutrition-manuals`
- **Response Body:**
```json
{
    "title": "Nutrition References (كراوس وغيره)",
    "references": [
        { "file_name": "Kraus_Chapter_1.pdf", "url": "..." },
        ...
    ],
    "external_links": [
        { "name": "USDA Food Database", "url": "..." }
    ]
}
```

---

## 3. Creating a Diet Plan (Updated)
Creates a full diet plan with integrated clinical notes.

- **Endpoint:** `POST /api/diet-plans`
- **Request Body:**
```json
{
    "patient_id": 5,
    "doctor_id": 2,
    "title": "خطة إنقاص وزن شهرية",
    "daily_calories": 2100,
    "duration_days": 30,
    "start_date": "2026-03-01",
    "end_date": "2026-03-31",
    "doctor_notes": [
        "اشرب 3 لتر ماء يومياً",
        "المشي نصف ساعة قبل الإفطار"
    ],
    "meals": [
        {
            "day_number": 1,
            "meal_type": "breakfast",
            "meal_name": "شوفان بالحليب",
            "calories": 400,
            "carbo": 50,
            "protin": 20,
            "fat": 10,
            "serving": "1 cup"
        }
    ]
}
```

---

## 4. Individual Meal Management (Updated)
Store specific portion details for meal tracking.

- **Endpoint:** `POST /api/meals`
- **Request Body:**
```json
{
    "diet_plan_id": 1,
    "meal_type": "lunch",
    "meal_name": "صدر دجاج مشوي",
    "carbo": 0,
    "protin": 30.5,
    "fat": 5.2,
    "energy": "200",
    "serving": "150g" 
}
```

---

## Technical Notes
- **Activity Levels:** `sedentary` (1.2), `low` (1.3), `moderate` (1.5), `active` (1.7), `very_active` (1.9).
- **Macro Logic:** 50% Carbs, 20% Protein, 30% Fat based on Physician Requirements.
- **TEF:** The system automatically adds 10% Thermic Effect of Food to the TDEE result.
