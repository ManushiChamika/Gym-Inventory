<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Register</title>
  <link rel="icon" href="images/favicon.png" type="image/png">
  <link rel="stylesheet" href="css/styleLogin.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">

</head>

<body>
  <div class="container">
    <div class="form-box box">

      <header>Sign Up</header>
      <hr>

      <form action="#" method="POST">

  <div class="form-box">

    <?php
    session_start();
    include "connection.php";

    if (isset($_POST['register'])) {
      $name = $_POST['username'];
      $email = $_POST['email'];
      $phone = $_POST['phone']; // Capture phone number
      $pass = $_POST['password'];
      $cpass = $_POST['cpass'];
      $age = $_POST['age'];
      $weight = $_POST['weight'];
      $height = $_POST['height'];

      // Validate phone number format
      if (!preg_match('/^\+94\d{9}$/', $phone)) {
        echo "<div class='message'>
        <p>Invalid phone number format. Use +94 followed by 9 digits.</p>
        </div><br>";
        echo "<a href='javascript:self.history.back()'><button class='btn'>Go Back</button></a>";
        exit();
      }

      $check = "select * from users where email='{$email}' OR phone='{$phone}'";
      $res = mysqli_query($conn, $check);

      $passwd = password_hash($pass, PASSWORD_DEFAULT);

      if (mysqli_num_rows($res) > 0) {
        echo "<div class='message'>
        <p>This email or phone number is already used. Try another one, please!</p>
        </div><br>";
        echo "<a href='javascript:self.history.back()'><button class='btn'>Go Back</button></a>";
      } else {
        if ($pass === $cpass) {
          $sql = "insert into users(username, email, phone, password, age, weight, height) 
                  values('$name','$email','$phone','$passwd', '$age', '$weight', '$height')";
          $result = mysqli_query($conn, $sql);

          if ($result) {
            echo "<div class='message'>
            <p>You are registered successfully!</p>
            </div><br>";
            echo "<a href='index.php'><button class='btn'>Ok</button></a>";
          } else {
            echo "<div class='message'>
            <p>Error occurred during registration.</p>
            </div><br>";
            echo "<a href='javascript:self.history.back()'><button class='btn'>Go Back</button></a>";
          }
        } else {
          echo "<div class='message'>
          <p>Password does not match.</p>
          </div><br>";
          echo "<a href='signup.php'><button class='btn'>Go Back</button></a>";
        }
      }
    } else {
    ?>

    <div class="input-container">
      <i class="fa fa-user icon"></i>
      <input class="input-field" type="text" placeholder="Username" name="username" required>
    </div>

    <div class="input-container">
      <i class="fa fa-envelope icon"></i>
      <input class="input-field" type="email" placeholder="Email Address" name="email" required>
    </div>

    <div class="input-container">
      <i class="fa fa-phone icon"></i>
      <input class="input-field" type="tel" placeholder="Phone Number (e.g., +94712345678)" name="phone" id="phone" pattern="\+94\d{9}" required>
    </div>

    <div class="input-container">
      <i class="fa fa-lock icon"></i>
      <input class="input-field password" type="password" placeholder="Password" name="password" required>
      <i class="fa fa-eye icon toggle"></i>
    </div>

    <div class="input-container">
      <i class="fa fa-lock icon"></i>
      <input class="input-field" type="password" placeholder="Confirm Password" name="cpass" required>
      <i class="fa fa-eye icon"></i>
    </div>

    <!-- New Fields for Age, Weight, and Height -->
    <div class="input-container">
      <i class="fa fa-calendar icon"></i>
      <input class="input-field" type="number" placeholder="Age" name="age" required>
    </div>

    <div class="input-container">
      <i class="fa fa-weight icon"></i>
      <input class="input-field" type="number" placeholder="Weight (kg)" name="weight" id="weight" required>
    </div>

    <div class="input-container">
      <i class="fa fa-ruler icon"></i>
      <input class="input-field" type="number" placeholder="Height (cm)" name="height" id="height" required>
    </div>

    <!-- Display BMI Result -->
    <div class="input-container">
      <i class="fa fa-heartbeat icon"></i>
      <input class="input-field" type="text" placeholder="Your BMI" id="bmiResult" disabled>
    </div>

    <center><input type="submit" name="register" id="submit" value="Signup" class="btn"></center>

    <div class="links">
      Already have an account? <a href="index.php">Signin Now</a>
    </div>

  </div>

</form>
      <?php
          }
          ?>
  </div>

  <script>
    const toggle = document.querySelector(".toggle"),
      input = document.querySelector(".password");
    toggle.addEventListener("click", () => {
      if (input.type === "password") {
        input.type = "text";
        toggle.classList.replace("fa-eye-slash", "fa-eye");
      } else {
        input.type = "password";
      }
    });

    // Function to calculate BMI
    function calculateBMI() {
      const weight = parseFloat(document.getElementById('weight').value);
      const height = parseFloat(document.getElementById('height').value);

      if (weight > 0 && height > 0) {
        const heightInMeters = height / 100;  // Convert height to meters
        const bmi = weight / (heightInMeters * heightInMeters);
        document.getElementById('bmiResult').value = bmi.toFixed(2);  // Display BMI with 2 decimal points
      } else {
        document.getElementById('bmiResult').value = '';  // Clear BMI if invalid input
      }
    }

    // Event listeners to trigger BMI calculation
    document.getElementById('weight').addEventListener('input', calculateBMI);
    document.getElementById('height').addEventListener('input', calculateBMI);
  </script>
  <script>
  document.getElementById('submit').addEventListener('click', function (event) {
    const phoneField = document.getElementById('phone');
    const phoneValue = phoneField.value;
    const phonePattern = /^\+94\d{9}$/;

    if (!phonePattern.test(phoneValue)) {
      event.preventDefault(); // Prevent form submission
      alert("Invalid phone number format. Please enter a number in the format +947XXXXXXXX.");
    }
  });
</script>
</body>

</html>
