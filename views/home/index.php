<?php
use App\Core\View;
?>

<div class="container">
    <h2><?= $title ?></h2>
    <p><?= $message ?></p>
    
    <div class="features">
        <h3>Features of this MVC Framework:</h3>
        <ul>
            <li>Simple and lightweight</li>
            <li>MVC architecture</li>
            <li>Routing system</li>
            <li>View templating</li>
            <li>Error handling</li>
        </ul>
    </div>
    
    <?= View::partial('sidebar', ['user' => 'Guest']) ?>
</div>