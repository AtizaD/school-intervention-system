# Student ID/Username Generation

## Overview
The **student_id** field in the database serves as the **student's username**. There is NO separate admission number - the student_id IS the username used for login and identification.

---

## Format
```
{CLASS}-{INITIALS}{3-DIGIT-SEQUENCE}
```

**Examples:**
- `1A-JD001` - John Doe in Class 1A (first student)
- `1A-JD002` - Jane Doe in Class 1A (second student with same initials)
- `2B-MKA001` - Mary Kate Anderson in Class 2B

---

## Database Schema

```sql
CREATE TABLE students (
    id INT AUTO_INCREMENT PRIMARY KEY,   -- Internal auto-increment ID
    student_id VARCHAR(20) UNIQUE NOT NULL,  -- This IS the username
    first_name VARCHAR(50) NOT NULL,
    last_name VARCHAR(50) NOT NULL,
    gender ENUM('male','female') NOT NULL,
    class VARCHAR(20) NOT NULL,
    house ENUM('1','2','3','4') NOT NULL,
    is_active TINYINT(1) DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);
```

**Key Points:**
- `id` = Internal primary key (auto-increment, never changes)
- `student_id` = Username (unique, can be changed if needed)
- Students use `student_id` to log in

---

## Identical Logic with Assessment System

### Assessment System
- **File:** `/var/www/assessment/register.php` (lines 106-169)
- **Field:** `Users.username`
- **Purpose:** Student login username

### Intervention System
- **File:** `/var/www/intervention.bassflix.xyz/classes/Student.php` (lines 115-219)
- **Field:** `students.student_id`
- **Purpose:** Student ID which IS their username

✅ **BOTH USE IDENTICAL GENERATION LOGIC**

---

## Generation Steps

### 1. Duplicate Detection
```php
// Check if student with same name exists in class
WHERE (
    (LOWER(first_name) = LOWER(:fname1) AND LOWER(last_name) = LOWER(:lname1))
    OR
    (LOWER(first_name) = LOWER(:fname2) AND LOWER(last_name) = LOWER(:lname2))
)
AND class = :class
```

### 2. Generate Initials from Sorted Names
```php
$nameParts = array_merge(
    explode(' ', $firstName),
    explode(' ', $lastName)
);
sort($nameParts, SORT_STRING | SORT_FLAG_CASE);

$initials = '';
foreach ($nameParts as $part) {
    $initials .= substr($part, 0, 1);
}
$initials = strtoupper($initials);
```

### 3. Create Base Username
```php
$baseUsername = trim($class) . '-' . $initials;
// Example: "1A-DJ"
```

### 4. Find Highest Suffix and Increment
```php
SELECT student_id FROM students
WHERE student_id LIKE '1A-DJ%'
ORDER BY CAST(SUBSTRING(student_id, -3) AS UNSIGNED) DESC
LIMIT 1

// If last is "1A-DJ005", next will be "1A-DJ006"
```

### 5. Generate Final Username
```php
$username = $baseUsername . str_pad($suffix, 3, '0', STR_PAD_LEFT);
// Result: "1A-DJ001"
```

---

## Test Cases

### Test 1: Simple Name
**Input:** First: "John", Last: "Doe", Class: "1A"
- Name parts: ["John", "Doe"]
- Sorted: ["Doe", "John"]
- Initials: "DJ"
- **Student ID:** `1A-DJ001`

### Test 2: Multiple Word Names
**Input:** First: "Mary Kate", Last: "Anderson", Class: "2B"
- Name parts: ["Mary", "Kate", "Anderson"]
- Sorted: ["Anderson", "Kate", "Mary"]
- Initials: "AKM"
- **Student ID:** `2B-AKM001`

### Test 3: Name Order Independence
**Option A:** First: "Kwame", Last: "Mensah"
**Option B:** First: "Mensah", Last: "Kwame"
- Both sorted to: ["Kwame", "Mensah"]
- Both get initials: "KM"
- **Student ID:** `3C-KM001` (same for both!)

### Test 4: Sequential Numbering
Adding three students with initials "DJ" in Class 1A:
- Student 1: `1A-DJ001`
- Student 2: `1A-DJ002`
- Student 3: `1A-DJ003`

---

## Usage

### Auto-Generate (Recommended)
Leave the student_id field blank when creating a student:

```php
$studentModel = new Student();
$result = $studentModel->create([
    'first_name' => 'John',
    'last_name' => 'Doe',
    'class' => '1A',
    'gender' => 'male',
    'house' => '1',
    // student_id is empty - will be auto-generated
]);

// Returns: ['success' => true, 'student_id' => '1A-DJ001']
```

### Manual Entry (Optional)
Provide custom student_id:

```php
$result = $studentModel->create([
    'student_id' => 'CUSTOM-001',  // Manual ID
    'first_name' => 'John',
    'last_name' => 'Doe',
    'class' => '1A',
    'gender' => 'male',
    'house' => '1'
]);
```

---

## Form Implementation

The add student form (`pages/students/add.php`) shows:
- Real-time preview of auto-generated student ID
- Optional manual entry
- Clear indication that student_id IS the username

```html
<label for="student_id">
    Student ID / Username
    <small class="text-muted">(Optional - Auto-generated)</small>
</label>
<input type="text" id="student_id" name="student_id"
       placeholder="Leave blank to auto-generate">
<small class="text-muted">
    <i class="fas fa-magic"></i> Will be generated automatically
    based on name and class (e.g., 1A-JD001)
</small>
```

---

## Important Notes

1. ✅ **student_id = username** - No separate admission number
2. ✅ **Identical to Assessment system** - Same students, same IDs
3. ✅ **Auto-generated by default** - Consistent format
4. ✅ **Manual override allowed** - For special cases
5. ✅ **Duplicate prevention** - Checks for existing students
6. ✅ **Name order independent** - "John Doe" = "Doe John"

---

**Last Updated:** 2025-11-08
**Status:** ✅ Fully Implemented and Tested
