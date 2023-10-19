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
</head>
<body>
    <header class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container">
            <a href="https://kubiq.com.my" class="navbar-brand">
                <img src="../static/img/kubiq_logo.png" alt="Kubiq Logo" height="50"/>
            </a>
            <button class="btn btn-primary" type="button">New Design</button>
            <form class="form-inline ml-auto">
                <div class="form-group">
                    <label for="total_price">Total (RM):</label>
                    <input type="text" class="form-control ml-1" id="total_price" placeholder="Total..." readonly>
                </div>
                <button class="btn btn-primary ml-1" type="button">Continue</button>
            </form>
        </div>
    </header>
    <main>
        <div class="d-flex flex-column flex-shrink-0 p-3 bg-light" style="width: 280px;">
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
                <a href="#" class="nav-link active" aria-current="page">
                  <svg class="bi me-2" width="16" height="16"><use xlink:href="#home"/></svg>
                  Home
                </a>
              </li>
              <li>
                <a href="#" class="nav-link link-dark">
                  <svg class="bi me-2" width="16" height="16"><use xlink:href="#speedometer2"/></svg>
                  Dashboard
                </a>
              </li>
              <li>
                <a href="#" class="nav-link link-dark">
                  <svg class="bi me-2" width="16" height="16"><use xlink:href="#table"/></svg>
                  Orders
                </a>
              </li>
              <li>
                <a href="#" class="nav-link link-dark">
                  <svg class="bi me-2" width="16" height="16"><use xlink:href="#grid"/></svg>
                  Products
                </a>
              </li>
              <li>
                <a href="#" class="nav-link link-dark">
                  <svg class="bi me-2" width="16" height="16"><use xlink:href="#people-circle"/></svg>
                  Customers
                </a>
              </li>
            </ul>
            <hr>
          </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>
