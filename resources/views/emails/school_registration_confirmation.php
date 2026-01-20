@component('mail::message')
# School Account Created Successfully

Hello **{{ $school->admin_name }}**,

Your school account has been successfully created in the **Edumall Inventory System**!

---

**School Details:**
- **School Name:** {{ $school->name }}
- **Centre Number:** {{ $school->centre_number }}
- **District:** {{ $school->district }}
- **Registration Date:** {{ $school->created_at->format('F j, Y') }}

---

Your account is now active and you can start managing your inventory through the system.

If you have any questions or need assistance, please contact our support team.

Welcome to Edumall!

**The Edumall Uganda Team**

@endcomponent
