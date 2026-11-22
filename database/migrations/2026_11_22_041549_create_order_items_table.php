<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // order_items table (ဘောင်ချာတစ်စောင်ချင်းစီထဲတွင် ပါဝင်သော ပစ္စည်းအသေးစိတ်စာရင်း) ကို စတင်ဆောက်လုပ်ခြင်း
        Schema::create('order_items', function (Blueprint $table) {
            // Order item တစ်ခုချင်းစီရဲ့ auto-increment primary key ID
            $table->id();

            // ဘယ်ဘောင်ချာ (Order) ထဲက ပစ္စည်းလဲဆိုတာ ချိတ်ဆက်ထားသော foreign key
            // Order ဖျက်လိုက်ပါက ၎င်းနှင့်သက်ဆိုင်သော item များပါ တစ်ပါတည်း ပျက်စီးစေရန် cascadeOnDelete() သုံးထားသည်
            $table->foreignId('order_id')->constrained('orders')->cascadeOnDelete();

            // ဘယ်ကုန်ပစ္စည်း (Product) ကို ရောင်းချလိုက်သလဲဆိုတာ ချိတ်ဆက်ထားသော foreign key
            // စာရင်းဟောင်းများ မပျက်စီးစေရန် restrictOnDelete() ဖြင့် ကာကွယ်ထားသည်
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();

            // ဝယ်ယူသည့် အရေအတွက် (အနှုတ်မဖြစ်နိုင်သော အပေါင်းကိန်းပြည့် integer)
            $table->unsignedInteger('quantity');

            // ဝယ်ယူခဲ့သည့်အချိန်က ပစ္စည်းတစ်ခုချင်းစီ၏ ဈေးနှုန်း (နောင်ဈေးတက်/ကျသွားလျှင်လည်း ဘောင်ချာဟောင်းဈေး မပြောင်းလဲစေရန် သိမ်းထားခြင်း)
            $table->decimal('unit_price', 10, 2);

            // စာရင်းသွင်းချိန်နှင့် ပြင်ဆင်ချိန် (created_at, updated_at)
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // rollback ပြုလုပ်ပါက order_items table ကို ပြန်ဖျက်ပေးခြင်း
        Schema::dropIfExists('order_items');
    }
};
