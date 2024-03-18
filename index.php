<!DOCTYPE html>
<html>

<head>
  <title>ERP Roelplant - Iniciar Sesión</title>
  <?php include "./class_lib/scripts.php"; ?>
  <?php include "./class_lib/links.php"; ?>
  <?php
      
      session_name("roel-erp");session_start();
      if (isset($_SESSION) && isset($_SESSION["roel-erp-token"]) && isset($_COOKIE["roel-erp-token"]) && ($_SESSION["roel-erp-token"] == $_COOKIE["roel-erp-token"])){
        echo "<script>
                document.location.href = 'inicio.php';
              </script>
        ";
      }
    ?>
  <style>
    .MainLogin {
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 90%;
      max-width: 300px;
      padding: 15px;
      box-sizing: border-box;
      box-shadow: 0 0 5px rgba(0, 0, 0, .2);
      border-top: 7px solid #2196F3;
      background-color: #fff;
      color: #777777;
    }

    .MainLogin button {
      background-color: #2196F3;
    }

    .MainLogin input {
      border-radius: 0;
    }
  </style>
</head>

<body onLoad="document.getElementById('UserName').focus();">
  <form class="AjaxForms MainLogin" id="loginform" data-type-form="login" autocomplete="off">
    <div align="center"><img src="dist/img/roel.jpg" style="width: 150px;height:75px;" /><br><h3>E.R.P</h3></div>
    <div class="form-group">
      <label class="control-label" for="UserName">Usuario</label>
      <input class="form-control" name="usuario" id="UserName" type="text" required="">
    </div>
    <div class="form-group">
      <label class="control-label" for="Pass">Contraseña</label>
      <input class="form-control" name="pass" id="Pass" type="password" required="">
    </div>
    <p class="text-center">
      <button type="submit" class="btn btn-primary btn-block">Ingresar</button>
    </p>
  </form>
  <div class="contenedor"></div>
</body>

<script type="text/javascript">
  let form = document.getElementById('loginform');
  form.addEventListener('submit', (event) => {
    // handle the form data
    event.preventDefault();
    const user = $("#UserName").val().trim();
    const pass = $("#Pass").val().trim();
    login(user, pass);
  });
  function login(user, pass) {
    $.ajax({
      url: 'valida_usr.php',
      type: 'POST',
      data: { user: user, pass: pass },
      success: function (x) {
        $(".contenedor").html(x);
      },
      error: function (jqXHR, estado, error) {
        swal("Error al Iniciar Sesión", error, "error");
      }
    });
  }
</script>

</html>