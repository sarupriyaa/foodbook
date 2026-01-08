<?php
?>
<style>
    .hero-text h1{
        color:white;
    }
    .hero-title{
        font-size:5rem;
        text-shadow: 2px 2px 10px rgba(0,0,0,0.6);
        background: rgba(0,0,0,0.4);

    }
</style>
<!DOCTYPE html>
<html>
<head>
    <title>RecipeBook - Home</title>
    <link rel="stylesheet" href="home.css">
</head>
<body>
<?php include "navbar.php"; ?> 
<div>
    <div class="div1">
        <div class="hero">
            <img class="hero-img" src="https://shef.com/homemade-food/wp-content/uploads/mediterranean-food-dinner-table-history.jpeg" alt="Delicious food">
            <div class="hero-text">
                <h1 class="hero-title">Food Your Way</h1>
            </div>
        </div>
    </div>

    <div class="div2">
        <div class="sub1">
            <h3 class="hero-title2">The easiest way to organize your recipes</h3>
            <p class="hero-text1">RecipeBook is a quick and easy way to collect, organize your favorite recipes across your devices.</p>
        </div>
        <div class="sub2">
            <img class="img1" src="https://cdn.prod.website-files.com/63bb7fe09d70bb7dc8e86719/63c4e43459c35a2889466e8d_776-x-528-Add.webp">
        </div>
    </div>

    <div class="div3">
        <div class="sub3">
            <h3 class="hero-title3">Discover new dishes you'll love</h3>
            <p class="hero-text2">Recipe communities help you share and discover recipes that fit your lifestyle.</p>
        </div>
        <div class="sub4">
            <img class="img2" src="https://is1-ssl.mzstatic.com/image/thumb/PurpleSource221/v4/b5/66/78/b56678bb-cd2d-6a53-649f-ab9ddfc5679a/49afd315-9b5b-4bf8-a528-8bdff9c77e21_iPad_Landscape_03.jpg/643x0w.jpg">
        </div>
    </div>

    <div class="div4">
        <div class="sub6">
            <h3 class="hero-title4">Save recipes from anywhere, instantly</h3>
            <p>Save, add, customize and organize recipes across all your devices.</p>
            <p>Save from your phone or computer, share from social media.</p>
        </div>
        <div class="sub5">
            <img class="img3" src="https://www.flavorish.ai/_next/static/media/features.fe323f07.png">
        </div>
    </div>

    <div class="div5">
        <div class="sub8">
            <img class="img4" src="https://samsungfood.com/wp-content/uploads/2023/08/improve_nutri_content.jpg">
        </div>
        <div class="sub7">
            <h2>Meet your health goals</h2>
            <p>Unlock nutritional info and calorie counts for any recipe you save—even your own homemade recipes.</p>
            <p class="para">Unlock recipe nutrition</p>
            <hr>
        </div>
    </div>

    <div class="div6">
        <div class="sub10">
            <h1 class="title">RecipeBook</h1>
            <h3>Your ultimate kitchen companion.</h3>
            <a href="login.php">
                <button class="button1">GET STARTED</button>
            </a>
            <p class="p1">RecipeBook is free to try — jump in today!</p>
        </div>
        <div class="sub9">
            <img class="img5" src="https://htmlburger.com/blog/wp-content/uploads/2023/03/Food-App-Design-Foodei-by-Ali-Husni.gif">
        </div>
    </div>
</div>
<?php include 'footer.php'; ?>

</body>
</html>
