# Automatic Fee Assignment

**Date:** 2025-11-08
**Status:** ✅ Fully Implemented and Tested

---

## Overview

When a new student is added to the system, a default fee amount is **automatically assigned** to them. This eliminates the need for manual fee assignment and ensures all students have fees recorded from day one.

---

## How It Works

### Automatic Process

1. **Student Created** → Admin adds a new student through the Add Student form
2. **Username Generated** → System generates unique student ID (e.g., 3A1-JM001)
3. **Fee Assigned** → System automatically creates a fee record with:
   - Default amount (from settings)
   - Calculated due date (enrollment date + due days)
   - Status: Pending
   - Balance: Full amount (since nothing is paid yet)

### Settings Configuration

Administrators can configure the automatic fee assignment in **Settings** page:

| Setting | Description | Default Value |
|---------|-------------|---------------|
| **Default Student Fee Amount** | Amount assigned to new students | GHS 500.00 |
| **Default Fee Due Days** | Days from enrollment to due date | 30 days |

---

## Settings Page

Navigate to: **Settings → System Settings → Fees Settings**

You'll see two configurable fields:

```
┌─────────────────────────────────────────────────┐
│ Fees Settings                                   │
├─────────────────────────────────────────────────┤
│                                                 │
│ Default Student Fee Amount                      │
│ ┌─────────────┐                                │
│ │ 500.00      │ [Number field]                 │
│ └─────────────┘                                │
│ Default fee amount to assign to new students    │
│                                                 │
│ Default Fee Due Days                            │
│ ┌─────────────┐                                │
│ │ 30          │ [Number field]                 │
│ └─────────────┘                                │
│ Number of days from enrollment for payment due  │
│                                                 │
│                       [Save All Settings] →     │
└─────────────────────────────────────────────────┘
```

---

## Example Scenario

### Scenario
Admin adds a new student on **November 8, 2025** with:
- **Default Fee Amount:** GHS 500.00
- **Due Days:** 30 days

### Result
Student fee record automatically created:

| Field | Value |
|-------|-------|
| Student ID | 3A2-AA001 |
| Amount Due | GHS 500.00 |
| Amount Paid | GHS 0.00 |
| Balance | GHS 500.00 |
| Due Date | December 8, 2025 (30 days from enrollment) |
| Status | Pending |

---

## Database Structure

### student_fees Table

```sql
fee_id          INT (auto-increment)
student_id      VARCHAR(20) (FK to students)
amount_due      DECIMAL(10,2)    -- From settings: default_student_fee_amount
amount_paid     DECIMAL(10,2)    -- Starts at 0.00
balance         DECIMAL(10,2)    -- Generated: amount_due - amount_paid
due_date        DATE             -- enrollment_date + default_fee_due_days
status          ENUM('pending','partial','paid','overdue')
created_by      INT (FK to users)
created_at      TIMESTAMP
updated_at      TIMESTAMP
```

---

## Code Implementation

### Student.php - assignDefaultFee()

Located in: `/classes/Student.php` (lines 310-347)

```php
private function assignDefaultFee($studentId) {
    try {
        // Get default fee amount from settings
        $defaultAmount = getSetting('default_student_fee_amount', 500.00);
        $dueDays = getSetting('default_fee_due_days', 30);

        // Calculate due date
        $dueDate = date('Y-m-d', strtotime("+{$dueDays} days"));

        // Get current user ID (who created the student)
        $createdBy = getCurrentUserId();

        // Insert fee record
        $this->db->insert('student_fees', [
            'student_id' => $studentId,
            'amount_due' => $defaultAmount,
            'amount_paid' => 0.00,
            'due_date' => $dueDate,
            'status' => 'pending',
            'created_by' => $createdBy
        ]);

        // Log the fee assignment
        logActivity('CREATE', 'student_fees', $studentId, null, [
            'amount_due' => $defaultAmount,
            'due_date' => $dueDate
        ]);

        return true;
    } catch (Exception $e) {
        error_log("Failed to assign default fee: " . $e->getMessage());
        return false;
    }
}
```

### Triggered From

`Student::create()` method (line 294):

```php
if ($insertId) {
    logActivity('CREATE', 'students', $data['student_id'], ...);

    // Automatically assign default fee amount to new student
    $this->assignDefaultFee($data['student_id']);

    return [
        'success' => true,
        'message' => MSG_CREATE_SUCCESS,
        'student_id' => $data['student_id'],
        'full_name' => $firstName . ' ' . $lastName
    ];
}
```

---

## Settings in Database

### Fee Settings Records

```sql
INSERT INTO settings (setting_key, setting_value, setting_type, description, category)
VALUES
    ('default_student_fee_amount', '500.00', 'number',
     'Default fee amount to assign to new students', 'fees'),
    ('default_fee_due_days', '30', 'number',
     'Number of days from enrollment for fee payment due date', 'fees');
```

---

## Testing Results

### Test Case: Create Student with Auto Fee Assignment

**Input:**
```php
Student: Akua Adjei
Class: 3A2
Gender: Female
House: 1
```

**Results:**
```
✅ Student created successfully!
   Student ID: 3A2-AA001
   Full Name: Akua Adjei

✅ Fee assigned automatically!
   Fee ID: 1
   Amount Due: GHS 500.00
   Amount Paid: GHS 0.00
   Balance: GHS 500.00
   Due Date: 2025-12-08 (30 days from Nov 8)
   Status: pending
   Created By: User ID 1
```

---

## Benefits

1. ✅ **Consistency** - All students have fees from day one
2. ✅ **Time Saving** - No manual fee entry required
3. ✅ **Accuracy** - Eliminates human error in fee assignment
4. ✅ **Flexibility** - Admins can adjust default amount anytime
5. ✅ **Audit Trail** - All fee assignments are logged
6. ✅ **Due Date Calculation** - Automatic based on enrollment date

---

## Changing Default Fee Amount

### Steps to Update

1. Navigate to **Settings** page
2. Scroll to **Fees Settings** section
3. Update **Default Student Fee Amount** (e.g., change from 500.00 to 600.00)
4. Update **Default Fee Due Days** if needed (e.g., change from 30 to 45)
5. Click **Save All Settings**

### Impact

- ✅ Changes apply to **new students only**
- ❌ Existing student fees are **not affected**
- ✅ Settings saved in database immediately
- ✅ No system restart required

---

## Student View

When viewing a student's record, the fee information is automatically displayed:

```
Student: Akua Adjei (3A2-AA001)
Class: 3A2, Gender: Female, House: 1

Fee Information:
├─ Amount Due: GHS 500.00
├─ Amount Paid: GHS 0.00
├─ Balance: GHS 500.00
├─ Status: Pending
└─ Due Date: December 8, 2025
```

---

## Future Enhancements (Optional)

Possible future improvements:

1. **Class-Based Fees** - Different amounts for different classes/levels
2. **Bulk Fee Updates** - Update fees for all students at once
3. **Fee Templates** - Pre-defined fee structures (e.g., boarding vs day)
4. **Installment Plans** - Split fees into multiple payments
5. **Grace Period** - Days after due date before status changes to overdue

---

## Troubleshooting

### Issue: Fee Not Assigned

**Possible Causes:**
1. Settings not configured (missing default_student_fee_amount)
2. Database insert failed
3. No logged-in user (created_by is null)

**Solution:**
- Check settings in database
- Review error logs
- Ensure user is logged in when creating student

### Issue: Wrong Fee Amount

**Cause:** Setting value incorrect or cached

**Solution:**
- Verify settings in Settings page
- Update and save settings
- Create test student to verify

---

## Technical Notes

### Why Balance is a Generated Column

```sql
balance DECIMAL(10,2) GENERATED ALWAYS AS (amount_due - amount_paid) STORED
```

Benefits:
- Always accurate (automatically calculated)
- No need to manually update on payment
- Database-level integrity

### Fee Status Logic

| Status | Condition |
|--------|-----------|
| `pending` | Balance > 0, Due date not reached |
| `partial` | 0 < Balance < Amount Due |
| `paid` | Balance = 0 |
| `overdue` | Balance > 0, Due date passed |

---

## Conclusion

The automatic fee assignment system ensures all students have fees recorded from the moment they're enrolled, saving time and ensuring consistency across the system.

**Last Updated:** 2025-11-08
**Status:** ✅ Production Ready
