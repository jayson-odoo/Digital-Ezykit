<!DOCTYPE html>
<html>
<head>
    <link href='https://fonts.googleapis.com/css?family=Inter' rel='stylesheet'/>
    <meta charset="utf-8" />
    <title>Kubiq Digital Ezykit</title>
    <meta name="description" content="app, web app, responsive, responsive layout, admin, admin panel, admin dashboard, flat, flat ui, ui kit, AngularJS, ui route, charts, widgets, components" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link rel="stylesheet" href="../styles/digital_ezykit.css">

</head>
<body>
  <script src="../js/main.js"></script>
  <header class="navbar navbar-expand-lg navbar-light bg-light">
      <a href="https://kubiq.com.my" class="navbar-brand">
          <img src="../static/img/kubiq_logo.png" alt="Kubiq Logo" height="50"/>
      </a>
      <ul class="nav nav-tabs">
        <li class="nav-item">
          <a class="nav-link active" aria-current="page" href="#">Design</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="kubiq_quotation.html">Quotation</a>
        </li>
      </ul>
      <button class="btn btn-primary ml-5" type="button" onclick="newDesign()">New Design</button>
      <form class="form-inline ml-auto">
          <div class="form-group">
              <label for="total_price">Total (RM):</label>
              <input type="text" class="form-control ml-1" id="total_price" placeholder="Total..." readonly>
          </div>
          <button class="btn btn-primary ml-1" type="button">Continue</button>
      </form>
  </header>
<div class="wrapper d-flex align-items-stretch">
    <nav id="sidebar">
        <div class="custom-menu">
            <button type="button" id="sidebarCollapse" class="btn btn-primary">
                <i class="fa fa-bars"></i>
                <span class="sr-only">Toggle Menu</span>
            </button>
        </div>
        <div class="p-4">
          <div class="input-group">
            <div class="form-outline">
              <!-- Search form -->
              <div class="md-form mt-0">
                <input class="form-control" type="text" placeholder="Search modules..." aria-label="Search">
              </div>
            </div>
          </div>
          <hr>
          <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
              <button class="btn btn-light btn-block text-left" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">
                <i class="fas fa-chevron-down"></i>
                Base
              </button>
              <div class="collapse" id="collapseExample">
                <ul class="list-group">
                  <li class="list-group-item btn btn-light" onclick="addItem({'name': 'QB4570', 'height': 45, 'width': 70})">
                    <div class="container">
                      <div class="row">
                        <div class="col align-middle">
                          <img src="../static/img/kubiq_logo.png" alt="Kubiq Logo" height="25"/>
                        </div>
                        <div class="col">
                          <div class="text-wrap">
                            <small>QB4570</small>
                          </div>
                          <div class="text-wrap">
                            <small>Base Unit 1 Door</small>
                          </div>
                          <div class="text-wrap">
                            <small>45x60x100</small>
                          </div>
                        </div>
                      </div>
                    </div>
                  </li>
                  <li class="list-group-item">QB6070</li>
                  <li class="list-group-item">QB8070</li>
                </ul>
              </div>
            </li>
          </ul>
          <hr>
        </div>
    </nav>
</div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
      (function ($) {
          "use strict";
          var fullHeight = function () {
              $(".js-fullheight").css("height", $(window).height());
              $(window).resize(function () {
                  $(".js-fullheight").css("height", $(window).height());
              });
          };
          fullHeight();
          $("#sidebarCollapse").on("click", function () {
              $("#sidebar").toggleClass("active");
          });
      })(jQuery);
    </script>
    
</body>
</html>
