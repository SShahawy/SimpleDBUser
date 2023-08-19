<?php
session_start();
if (isset($_POST) && !empty($_POST)) {
  if (isset($_POST['csrf_token'])) {
    if ($_POST['csrf_token'] == $_SESSION['csrf_token']) {
    } else {
      echo "Problem";
    }
  }
  $max_time = 60 * 60 * 24;
  if (isset($_SESSION['csrf_token_time'])) {
    $token_time = $_SESSION['csrf_token_time'];
    if (($token_time + $max_time) >= time()) {
      $image_file = $_FILES["image"];

      // Exit if no file uploaded
      if (!isset($image_file)) {
        die('No file uploaded.');
      }

      // Exit if image file is zero bytes
      if (filesize($image_file["tmp_name"]) <= 0) {
        die('Uploaded file has no contents.');
      } elseif (filesize($image_file["tmp_name"]) > 2097152) {
        die('Cant be more than 2MB');
      }

      // Exit if is not a valid image file
      $image_type = exif_imagetype($image_file["tmp_name"]);
      if (!$image_type) {
        die('Uploaded file is not an image.');
      }

      // Get file extension based on file type, to prepend a dot we pass true as the second parameter
      $image_extension = image_type_to_extension($image_type, true);

      // Create a unique image name
      $image_name = bin2hex(random_bytes(16)) . $image_extension;

      // Move the temp image file to the images directory
      move_uploaded_file(
        // Temp image location
        $image_file["tmp_name"],

        // New image location
        __DIR__ . "/images/" . $image_name
      );


      $servername = "localhost";
      $username = "root";
      $password = "1111";
      $dbname = "rescapital";

      // Create connection
      $conn = new mysqli($servername, $username, $password, $dbname);
      // Check connection
      if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
      }

      $sql = "INSERT INTO users (fname, lname, image)
VALUES ('" . $_POST['fname'] . "', '" . $_POST['lname'] . "', '/images/" . $image_name . "')";

      if ($conn->query($sql) === TRUE) {
        echo "New record created successfully";
      } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
      }

      $conn->close();
    } else {
      unset($_SESSION['csrf_token']);
      unset($_SESSION['csrf_token_time']);
      echo "CSRF Expired";
    }
  }
}
$token = md5(uniqid(rand(), true));
$_SESSION['csrf_token'] = $token;
$_SESSION['csrf_token_time'] = time();
?>
<!DOCTYPE html>
<html class="js">

<head>
  <meta charset="utf-8">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>RES Capital</title>

  <!-- Stylesheets -->

  <link rel="stylesheet" href="css/bootstrap.min.css">
  <link rel="stylesheet" href="css/ionicons.min.css">
  <link rel="stylesheet" href="css/animate.css">
  <link rel="stylesheet" href="css/style.css">

  <!-- Google Fonts -->

  <link href='http://fonts.googleapis.com/css?family=Josefin+Sans:100,300,400,600,700,100italic,300italic,400italic,600italic,700italic|Cinzel:400,700,900' rel='stylesheet' type='text/css'>
</head>

<body>
  <form action="" class="form-group" method="post" enctype="multipart/form-data">
    <input type="hidden" name="csrf_token" value="<?php echo $token; ?>">
    <div class="col-md-12">
      <div class="col-md-4 col-xs-12">
        <label for="fname">First Name
          <input type="text" id="fname" required class="form-control" name="fname" placeholder="Enter first name"></label>
      </div>
      <div class="col-md-4 col-xs-12">
        <label for="lname">Last Name
          <input type="text" id="lname" required class="form-control" name="lname" placeholder="Enter last name"></label>
      </div>
      <div class="col-md-4 col-xs-12">
        <label for="image">Image
          <input type="file" id="image" required class="form-control" name="image" placeholder="Your image" onchange="previewImage(event)"></label>
        <img id="preview" alt="Preview Image" width="400" height="400">
      </div>
      <center>
        <input type="submit" value="Create" class="btn-lg btn-primary" style="padding:10px 30px">

      </center>
    </div>
  </form>
</body>


<script src="js/modernizr.custom.js"></script>
<script src="js/jquery.min.js"></script>
<script src="js/bootstrap.min.js"></script>
<script src="js/jquery.videoBG.js"></script>
<script src="js/jquery.sticky-kit.min.js"></script>
<script src="js/wow.min.js"></script>
<script src="js/script.js"></script>
<script src="https://unpkg.com/sweetalert/dist/sweetalert.min.js"></script>
<script>
  /* this function will call when page loaded successfully */
  $(document).ready(function() {

    /* this function will call when onchange event fired */
    $("#image").on("change", function() {

      /* current this object refer to input element */
      var $input = $(this);

      /* collect list of files choosen */
      var files = $input[0].files;

      var filename = files[0].name;

      /* getting file extenstion eg- .jpg,.png, etc */
      var extension = filename.substr(filename.lastIndexOf("."));

      /* define allowed file types */
      var allowedExtensionsRegx = /(\.jpg|\.jpeg|\.png|\.gif)$/i;

      /* testing extension with regular expression */
      var isAllowed = allowedExtensionsRegx.test(extension);

      if (isAllowed) {
        swal({
          title: "Good job!",
          text: "File type is valid for the upload!",
          icon: "success",
          button: "Aww yiss!",
        });
        $("#preview").css("display", "block");
        /* file upload logic goes here... */
      } else {
        swal({
          title: "Too Bad!",
          text: "File type is not allowed",
          icon: "error",
          button: "Aww NO!",
        });
        $("#preview").css("display", "none");
        return false;
      }
    });



  });

  function previewImage(event) {
    var input = event.target;
    var image = document.getElementById('preview');
    if (input.files && input.files[0]) {
      var reader = new FileReader();
      reader.onload = function(e) {
        image.src = e.target.result;
      }
      reader.readAsDataURL(input.files[0]);
    }
  }
</script>
</body>

</html>