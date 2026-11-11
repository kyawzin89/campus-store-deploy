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
                Schema::create('categories', function (Blueprint $table) {
            // Category ရဲ့ auto-increment ID နံပါတ် (1, 2, 3...)
            $table->id();

            // Category နာမည် (ဥပမာ- Drinks, Snacks)၊ နာမည်ထပ်မနေအောင် unique() ခံထားသည်
            $table->string('name')->unique();

            // စာရင်းစသွင်းချိန်နဲ့ ပြင်ဆင်ချိန် (created_at, updated_at) ကို အလိုအလျောက်မှတ်ပေးခြင်း
            $table->timestamps();
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
