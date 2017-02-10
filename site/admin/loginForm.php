<?php
  $loginMessage = (isset($loginMessage))
                ? $loginMessage.'<br>Userlogin:'
                : 'Userlogin:';
?><div class="container">
  <form class="form-signin" method="post" action="index.php?action=login">
    <h2 class="form-signin-heading"><?php echo $loginMessage; ?></h2>
    <input name="username" type="text" class="input-block-level" placeholder="Username" autofocus>
    <input name="password" type="password" class="input-block-level" placeholder="Password">
    <button class="btn btn-large btn-primary" type="submit">Login</button>
  </form>
</div>
