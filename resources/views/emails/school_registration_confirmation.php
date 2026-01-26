@component('mail::message')
# Welcome to Edumall! 🎉

Hello **{{ $school->admin_name }}**,

Thank you for trusting **Edumall Uganda** to help you manage your school's inventory and stock!

Your school account has been successfully created in the **Edumall Inventory System**, and we're excited to have you on board.

---

**School Details:**
- **School Name:** {{ $school->name }}
- **Centre Number:** {{ $school->centre_number }}
- **District:** {{ $school->district }}
- **Registration Date:** {{ $school->created_at->format('F j, Y') }}

---

Your account is now active and you can start managing your inventory through our user-friendly system. We're here to support you every step of the way in keeping your school's resources organized and accessible.

If you have any questions or need assistance getting started, please don't hesitate to contact our support team.

Welcome to the Edumall family!

**The Edumall Uganda Team**

@endcomponent
