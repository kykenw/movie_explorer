$(document).ready(function () {
    $('.carousel').carousel();
  $("#searchBox").keyup(function () {
      var query = $("#searchBox").val();

      if (query.length > 2) {
          $.ajax(
              {
                  url: 'index.php',
                  method: 'POST',
                  data: {
                      search: 1,
                      q: query
                  },
                  success: function (data) {
                     $("#response").html(data);
                  },
                  dataType: 'text'
              }
          );
      }else if(query.length <= 2) {
        $("#response").html("");
      }
  });
  $(document).on('click', '.addbtn', function () {
    var title = $("#title").val();
    var posterpath = $("#posterpath").val();
    var url = $("#url").val();
    console.log({
        "title": title,
        "posterpath": posterpath,
        "url": url
    });
    $.ajax(
        {
            url: 'index.php',
            method: 'POST',
            data: {
                title: title,
                posterpath: posterpath,
                url: url
            },
            success: function (data) {
                Materialize.toast(data, 2000)
            },
            dataType: 'text'
        }
    );

  });
  $(document).on('click', '.sblink', function () {
      var name = $(this).text();
      var id = $(this).attr('id');
      var url = "http://localhost/phpfinalproject/fp/movie.php?movieId=" + id;
      $("#searchBox").val(name);
      $("#response").html("");
      window.location = url;
  });
  $(document).on('click', '#login', function() {
    var username = $("#username").val();
    var password = $("#password").val();

    $.ajax(
        {
            url: 'login.php',
            method: 'POST',
            data: {
                login_username: username,
                login_password: password
            },
            success: function (error) {
                if(error) {
                    $("#error").addClass("red");
                    $("#error").html(error);
                }else {
                    url = 'http://localhost/phpfinalproject/fp/index.php';
                    window.location = url;
                }
            },
            dataType: 'text'
        }
    )
  });

  $(document).on('click', '#register', function() {
    var username = $("#username").val();
    var password = $("#password").val();

    $.ajax(
        {
            url: 'register.php',
            method: 'POST',
            data: {
                register_username: username,
                register_password: password
            },
            success: function (error) {
                if(error) {
                    $("#error").addClass("red");
                    $("#error").html(error);
                }else {
                    url = 'http://localhost/phpfinalproject/fp/login.php';
                    window.location = url;
                }
            },
            dataType: 'text'
        }
    )
  });

  $(document).on('click', '.remove', function () {
    var id = $(this).attr('id');

    console.log({
        "id": id,
    });
    $.ajax(
        {
            url: 'index.php',
            method: 'POST',
            data: {
                id: id
            },
            success: function (data) {
               console.log(data);
               $("#watchlist").html(data)
            },
            dataType: 'text'
        }
    );

  });
});