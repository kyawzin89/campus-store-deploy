# DECISIONS.md: Trust Design Decisions

This document records the key technical and architectural decisions made during the development of the Trust POS System, including alternatives considered and the reasoning behind each decision.

## 1. Storing User Roles in a Single Table
### (User Role များကို Table တစ်ခုတည်းတွင် သိမ်းဆည်းခြင်း)

**Decided:**

A single `role` column (`admin` or `cashier`) was added to the `users` table. Access to administrative features is restricted using the `EnsureUserIsAdmin` middleware.

*`users` table အတွင်းတွင် `admin` နှင့် `cashier` ဟူသော Role နှစ်မျိုးကို သတ်မှတ်နိုင်သည့် `role` column တစ်ခု ထည့်သွင်းထားပါသည်။ Admin လုပ်ဆောင်ချက်များကို ဝင်ရောက်အသုံးပြုခွင့်အား `EnsureUserIsAdmin` middleware ဖြင့် ထိန်းချုပ်ထားပါသည်။*

**Considered instead:**

Creating separate `roles` and pivot tables or integrating a third-party permission management package.

*သီးခြား `roles` table နှင့် pivot table များ တည်ဆောက်ခြင်း သို့မဟုတ် Third-party Permission Management Package တစ်ခုကို အသုံးပြုခြင်းတို့ကိုလည်း စဉ်းစားခဲ့ပါသည်။*

**Why:**

Since the system only requires two user roles, storing the role directly in the `users` table provides a simpler database structure, reduces unnecessary joins, and makes role management easier.

*System တွင် User Role နှစ်မျိုးသာ လိုအပ်သောကြောင့် `users` table အတွင်း တိုက်ရိုက်သိမ်းဆည်းခြင်းသည် Database Structure ကို ပိုမိုရိုးရှင်းစေပြီး မလိုအပ်သော Table Join များကို လျှော့ချနိုင်သည့်အပြင် Role Management ကိုလည်း လွယ်ကူစေပါသည်။*

---

## 2. Locking Stock During Checkout
### (ငွေရှင်းချိန်တွင် Stock ကို Database Lock ချခြင်း)

**Decided:**

The checkout process was implemented using `DB::transaction()` together with `lockForUpdate()` to ensure stock quantities are updated safely and consistently.

*Checkout လုပ်ငန်းစဉ်အတွင်း Stock အရေအတွက်များကို မှန်ကန်လုံခြုံစွာ Update ပြုလုပ်နိုင်ရန် `DB::transaction()` နှင့် `lockForUpdate()` တို့ကို တွဲဖက်အသုံးပြုထားပါသည်။*

**Considered instead:**

Checking product availability only on the frontend without applying database-level row locking.

*Database-Level Row Locking အသုံးမပြုဘဲ Frontend ပေါ်တွင်သာ ပစ္စည်းလက်ကျန်အရေအတွက်ကို စစ်ဆေးရန် စဉ်းစားခဲ့ပါသည်။*

**Why:**

Database transactions and row-level locking help prevent concurrent checkout requests from selling the same remaining stock. If an error occurs during checkout, the transaction can be rolled back to prevent incomplete or inconsistent sales records.

*ငွေကိုင်ဝန်ထမ်းနှစ်ဦး သို့မဟုတ် ထို့ထက်ပိုသော ဝန်ထမ်းများက တစ်ချိန်တည်း Checkout ပြုလုပ်သည့်အခါ လက်ကျန်ပစ္စည်းတစ်ခုတည်းကို အကြိမ်ကြိမ် ရောင်းချမိခြင်းမှ ကာကွယ်ပေးပါသည်။ ထို့အပြင် Checkout လုပ်ငန်းစဉ်အတွင်း Error ဖြစ်ပေါ်ပါက Transaction ကို Rollback ပြုလုပ်နိုင်သဖြင့် မပြည့်စုံသော သို့မဟုတ် မကိုက်ညီသော အရောင်းမှတ်တမ်းများ ဖြစ်ပေါ်ခြင်းကို လျှော့ချနိုင်ပါသည်။*

---

## 3. Storing Item Prices in `order_items`
### (`order_items` Table အတွင်း ရောင်းချသည့် ဈေးနှုန်းကို သိမ်းဆည်းခြင်း)

**Decided:**

The `unit_price` of each product is stored directly in the `order_items` table at the time an order is created.

*Order တစ်ခု ဖန်တီးသည့်အချိန်တွင် ပစ္စည်းတစ်ခုချင်းစီ၏ ရောင်းချသည့် ဈေးနှုန်း (`unit_price`) ကို `order_items` table အတွင်း တိုက်ရိုက်သိမ်းဆည်းထားပါသည်။*

**Considered instead:**

Retrieving the current product price from the `products` table whenever historical orders or receipts are displayed.

*ယခင် Order များ သို့မဟုတ် ဘောက်ချာများကို ပြသသည့်အခါ `products` table မှ လက်ရှိဈေးနှုန်းကို ပြန်လည်ရယူအသုံးပြုရန် စဉ်းစားခဲ့ပါသည်။*

**Why:**

Storing the unit price at the time of purchase preserves historical sales accuracy. Future price changes made by administrators will not affect previous receipts, order records, or sales totals.

*ဝယ်ယူသည့်အချိန်က ဈေးနှုန်းကို သီးခြားသိမ်းဆည်းထားခြင်းဖြင့် ယခင်အရောင်းမှတ်တမ်းများ၏ တိကျမှုကို ထိန်းသိမ်းနိုင်ပါသည်။ Admin မှ နောင်တွင် Product ဈေးနှုန်းများ ပြောင်းလဲသတ်မှတ်သော်လည်း ယခင်ဘောက်ချာများ၊ Order မှတ်တမ်းများနှင့် အရောင်းစုစုပေါင်း ပမာဏများအပေါ် သက်ရောက်မှုရှိမည် မဟုတ်ပါ။*

---

## 4. Using Soft Deletes for Products
### (Product များအတွက် Soft Delete အသုံးပြုခြင်း)

**Decided:**

Laravel's `SoftDeletes` feature was enabled for the `Product` model instead of permanently deleting product records from the database.

*Product မှတ်တမ်းများကို Database မှ အပြီးတိုင်ဖျက်ပစ်ခြင်းအစား Laravel ၏ `SoftDeletes` Feature ကို `Product` Model တွင် အသုံးပြုထားပါသည်။*

**Considered instead:**

Permanently deleting product records using hard deletes.

*Product အချက်အလက်များကို Database မှ အပြီးတိုင် ဖျက်ပစ်သည့် Hard Delete နည်းလမ်းကိုလည်း စဉ်းစားခဲ့ပါသည်။*

**Why:**

Soft Deletes allow discontinued or inactive products to be excluded from the active POS product listings while preserving their historical records and relationships with previous orders. This helps maintain accurate sales history and supports future record recovery if necessary.

*Soft Delete အသုံးပြုခြင်းဖြင့် ရောင်းချမှုရပ်ဆိုင်းထားသော သို့မဟုတ် အသုံးမပြုတော့သော Product များကို POS ၏ လက်ရှိ Product စာရင်းမှ ဖယ်ရှားထားနိုင်သော်လည်း ယခင် Order များနှင့် ဆက်စပ်နေသော မှတ်တမ်းများကို ဆက်လက်ထိန်းသိမ်းထားနိုင်ပါသည်။ ထို့ကြောင့် အရောင်းမှတ်တမ်းများ၏ တိကျမှုကို ထိန်းသိမ်းနိုင်သည့်အပြင် လိုအပ်ပါက ယခင်မှတ်တမ်းများကို ပြန်လည်ရယူနိုင်ပါသည်။*

---

## 5. Using SQLite and Tailwind CSS CDN for Deployment
### (Deployment အတွက် SQLite နှင့် Tailwind CSS CDN အသုံးပြုခြင်း)

**Decided:**

SQLite was selected as the database, with persistent storage configured through a Railway volume mounted at `/app/storage`. Tailwind CSS was integrated through its CDN, and the application was packaged using a PHP Dockerfile for deployment.

*Database အဖြစ် SQLite ကို ရွေးချယ်အသုံးပြုထားပြီး Data များကို ဆက်လက်ထိန်းသိမ်းနိုင်ရန် Railway Persistent Volume ကို `/app/storage` တွင် Mount ပြုလုပ်ထားပါသည်။ Tailwind CSS ကို CDN မှတစ်ဆင့် အသုံးပြုထားပြီး Application ကို Deployment ပြုလုပ်နိုင်ရန် PHP Dockerfile ဖြင့် တည်ဆောက်ထားပါသည်။*

**Considered instead:**

Using a separately hosted MySQL database server and executing `npm run build` as part of the deployment process.

*MySQL Database Server သီးခြားတည်ဆောက်အသုံးပြုခြင်းနှင့် Deployment ပြုလုပ်သည့်အချိန်တွင် `npm run build` ကို လုပ်ဆောင်ခြင်းတို့ကိုလည်း စဉ်းစားခဲ့ပါသည်။*

**Why:**

This approach reduces deployment complexity and avoids potential memory-related failures associated with Node.js asset compilation on Railway. Using a persistent Railway volume also ensures that SQLite database files remain available across application restarts and redeployments.

*ဤနည်းလမ်းသည် Deployment လုပ်ငန်းစဉ်ကို ပိုမိုရိုးရှင်းစေပြီး Railway Platform ပေါ်တွင် Node.js အသုံးပြု၍ Asset Build ပြုလုပ်ရာ၌ ဖြစ်ပေါ်နိုင်သော Memory ပြဿနာများကို ရှောင်ရှားနိုင်ပါသည်။ ထို့အပြင် Persistent Railway Volume အသုံးပြုထားခြင်းကြောင့် Application ကို Restart သို့မဟုတ် Redeploy ပြုလုပ်သည့်အခါတွင်လည်း SQLite Database အတွင်းရှိ Data များကို ဆက်လက်ထိန်းသိမ်းထားနိုင်ပါသည်။*

---
