<div class="letterhead">
    <div class="hr_order_logo_img">
        <img src="{{ url('/images/logo_temiryol.png') }}">
    </div>
    <div class="org-name">
        "O`ZBEKISTON TEMIR YO`LLARI"<br>
        AKSIYADORLIK JAMIYATI<br>
        ISHLARNI BOSHQARISH DIREKSIYASI
    </div>

    <div class="line" aria-hidden="true"></div>
    <div class="address">
        100060, Toshkent sh., T. Shevchenko ko‘chasi, 7-uy
        Тел: +99871-238-88-19 &nbsp; &nbsp;<br> Факс: +99871-237-81-24
    </div>
    <div class="line" aria-hidden="true"></div>
</div>
<style>
    .hr_order_logo_img {
        text-align: center; /* Rasmni markazda joylashtiradi */
    }

    .hr_order_logo_img img {
        width: 100%;         /* Rasm eni konteynerning 100% bo'ladi */
        max-width: 120px;    /* Ixtiyoriy: maksimal o‘lchamni cheklaydi */
        height: auto;        /* Proporsiyani saqlaydi */
        display: inline-block;
    }

    .letterhead {
        max-width: 100%;
        margin: 0 auto;
    }

    .letterhead .line {
        height: 3px;
        background: #006ec5;
        border-radius: 1px;
    }

    .org-name {
        text-align: center;
        margin: 12px 0 6px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-size: 18px;
    }

    .letterhead .address {
        text-align: center;
        font-size: 13px;
        line-height: 1.25;
        margin: 6px 0 12px;
    }
</style>
