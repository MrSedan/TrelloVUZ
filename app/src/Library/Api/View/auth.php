<!DOCTYPE html>
<html lang="ru">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Auth page</title>
  <script src='/assets/auth.js'></script>
</head>

<body>
  <main>
    <h1>Auth</h1>
    <p id='message'></p>

<?php if ($user !== null): ?>
    <p>Signed in as <b><?= htmlspecialchars($user->name) ?></b> (<?= htmlspecialchars($user->email) ?>)</p>
    <form id='logout-form' action='/logout' method='post'>
      <button type='submit'>Logout</button>
    </form>
<?php else: ?>
    <h2>Login</h2>
    <form action='/login' method='post'>
      <p><input type='email' name='email' placeholder='Email' required></p>
      <p><input type='password' name='password' placeholder='Password' required></p>
      <button type='submit'>Login</button>
    </form>

    <h2>Register</h2>
    <form action='/register' method='post'>
      <p><input type='text' name='name' placeholder='Name' required></p>
      <p><input type='email' name='email' placeholder='Email' required></p>
      <p><input type='password' name='password' placeholder='Password (min 8)' required></p>
      <button type='submit'>Register</button>
    </form>
<?php endif; ?>
  </main>
</body>

</html>
