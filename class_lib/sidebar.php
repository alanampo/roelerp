<section class="sidebar">

          <!-- Sidebar user panel (optional) -->
          <div class="user-panel">
            <div class="pull-left image">
              <img src="dist/img/avatar.png" class="img-circle" alt="User Image">
            </div>
            <div class="pull-left info">
              <p><?php echo $_SESSION['nombre_de_usuario'] ?></p>
              <!-- Status -->
              <a href="#"><i class="fa fa-circle text-success"></i> Conectado</a>
            </div>
          </div>


          <!-- Sidebar Menu -->
          <ul class="sidebar-menu">
            <li class="treeview active">
              <a href="#"><i class="fa fa-bars"></i> <span>Módulos</span> <i class="fa fa-angle-left pull-right"></i></a>
              <ul class="treeview-menu" id="contenedor_modulos">
                
              </ul>
            </li>

            <li id="contenedor_panel" class="treeview active">
              
            </li>

          </ul><!-- /.sidebar-menu -->
        </section>
