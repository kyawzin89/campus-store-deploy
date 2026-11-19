<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
       /**
     * Run the migrations.
     */
    public function up(): void
    {
        // orders table (အရောင်းဘောင်ချာ မှတ်တမ်း) ကို စတင်ဆောက်လုပ်ခြင်း
        Schema::create('orders', function (Blueprint $table) {
            // Order တစ်ခုချင်းစီရဲ့ auto-increment primary key ID (Receipt နံပါတ်)
            $table->id();

            // ဘယ် Cashier ရောင်းချခဲ့သလဲဆိုတာ မှတ်သားဖို့ users table နှင့် ချိတ်ဆက်ခြင်း
            // User ဖျက်လိုက်သော်လည်း အရောင်းမှတ်တမ်း မပျက်စေရန် restrictOnDelete() ခံထားသည်
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();

            // ဘောင်ချာတစ်စောင်လုံးရဲ့ စုစုပေါင်းကျသင့်ငွေ (ဒသမ ၂ နေရာအထိ တိကျသော decimal)
            $table->decimal('total_amount', 10, 2);

            // အရောင်းအခြေအနေ (ပုံမှန်အားဖြင့် completed ဟု အလိုအလျောက် သတ်မှတ်သည်)
            $table->string('status')->default('completed');

            // ဘောင်ချာဖွင့်ချိန်နှင့် ပြင်ဆင်ချိန် (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // rollback ပြုလုပ်ပါက orders table ကို ပြန်ဖျက်ပေးခြင်း
        Schema::dropIfExists('orders');
    }

};
