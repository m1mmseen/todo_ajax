<!--
 ______________________________________________________
|         Folgende Quellen wurden verwendet          |
|                                                    |
|                        AJAX                        |
|   https://www.w3schools.com/js/js_ajax_intro.asp   |
|https://www.w3schools.com/jquery/jquery_ref_ajax.asp|
|     https://www.w3schools.com/xml/ajax_php.asp     |
|      UDEMY Kurs: Ajax und jQuery für Beginner      |
|                                                    |
|                        php                         |
|        https://www.php-einfach.de/experte/         |
|            https://www.php.net/docs.php            |
|                                                    |
|                       jQuery                       |
|              https://api.jquery.com/               |
|                                                    |
______________________________________________________ -->

<?php
require_once "inc/db.php";
?>

<!doctype html>
<html lang="de">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TO-DO WebApp</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.3/jquery.min.js"></script>

</head>
<style>

    .todo-items {
        display: inline-block;
        margin: .5rem;
    }

    .new-entry-btn, .delete-btn {
        border: none;
        background: transparent;
    }

</style>
<body>

<!-- LOGIN CONTENT START -->
<div id="login-container">
    <form id="login-form">
        <label for="benutzer">Benutzername</label>
        <input type="text" name="benutzer" id="benutzer">
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
        <button type="button" class="login-btn" id="login-btn">Login</button>
    </form>
</div>
<!-- LOGIN CONTENT ENDE -->

<!-- TODO CONTENT START -->
<div class="todos" id="todos" style="visibility: hidden">
    <div class="todo-form" id="todo-form">
        <form id="neues-todo">
            <div>
                <label for="todo"></label>
                <input class="todo-eintrag" name="todo" id="todo" placeholder="To-Do eintragen...">
                <button type="button" class="new-entry-btn" id="new-entry-btn">&#10149;</button>
            </div>
        </form>
    </div>

    <div class="todo-item-list" id="todo-item-list">

    </div>
    <div class="todo-items-controls" id="todo-items-controls">
        <button class="delete-all-btn" id="delete-all-btn">alle TO-Dos löschen</button>
        <button class="logout-btn" id="logout-btn">Logout</button>
    </div>
</div>
<!-- TODO CONTENT ENDE -->

<script>
    //____ GLOBAL VARIABLES____
    let ajaxType = "POST";
    let ajaxUrl = "inc/db.php";

    // ______ DOCUMENT READY FUNCTION(S)_____
    $(function () {
        // ------ LOGIN REQUEST START ------
        $('#login-btn').on("click",function (event) {
            //event.preventDefault();
            $.when(
                $.ajax({
                    url: ajaxUrl,
                    type: "POST",
                    data: {
                        type: "login",
                        benutzer: $('input[name="benutzer"]').val(),
                        password: $('input[name="password"]').val()
                    }
                }).done(function (res) {
                    let resJSON = JSON.parse(res);
                    if (resJSON.status !== "error") {

                        $('#login-container').css("display", "none");
                        $('#todos').css("visibility", "visible");
                        $('input').val('');

                    } else {
                        alert("Du konntest nicht angemeldet werden");
                    }
                }).fail(function (err) {
                    alert("Abbruch wegen Fehler(" + err.status + " " + err.statusText + " ");
                }),
                $.ajax({
                    url: ajaxUrl,
                    type: ajaxType,
                    data: {
                        type: "select",
                    }
                }).done(function (res) {
                    let resJSON = JSON.parse(res);
                    console.log(resJSON);

                    if (resJSON.status !== "error") {
                        if (resJSON.length > 0) {
                            for (let i = 0; i < resJSON.length; i++) {
                                $("#todo-item-list").append('<div class="todo-container" id="' + resJSON[i].id + '">'
                                    + '<button class="todo-items delete-btn" id="delete-btn">&#10008 </button>'
                                    + '<p class="todo-items">' + resJSON[i].Datum + " " + resJSON[i].todo + '</p>')
                            }
                        } else {
                            $("#todo-item-list").append('<p id="no-entries">Keine Einträge</p>');
                        }

                    } else {
                        alert("Daten konnten nicht geladen werden");
                    }
                }).fail(function (err) {
                    alert("Abbruch wegen Fehler(" + err.status + " " + err.statusText + " ");
                }),
            )
        });
        // LOGIN ENDE

        // ------ INSERT REQUEST START ------
        $('#new-entry-btn').on("click",function (event) {
            event.preventDefault();

            $.ajax({ cache: false,
                url: ajaxUrl,
                type: ajaxType,
                data: {
                    type: "new-todo",
                    todo: $('input[name="todo"]').val().trim() }
            }).done(function (res) {
                let resJSON = JSON.parse(res);
                console.log(resJSON.length);

                if (resJSON.status !== "error") {
                    if (document.getElementById("no-entries")) {
                        $('#no-entries').remove();
                    }
                    $('#todo-item-list').append('<div class="todo-container" id="' + resJSON.ID + '">'
                        + '<button class="todo-items delete-btn" id="delete-btn">&#10008 </button>'
                        + '<p class="todo-items">' + resJSON.Datum + " " + resJSON.todo + '</p>');
                    $('input[name="todo"]').val('');


                } else {
                    alert("Eintrag konnte nicht hinzugefügt werden");
                }
            }).fail(function (err) {
                alert("Abbruch wegen Fehler(" + err.status + " " + err.statusText + " ");
            });
        });
        // INSERT ENDE

        // ------ DELETE REQUEST START ------
        $('#todo-item-list').on("click", "button", function () {

            let id = $(this).closest('div').attr('id');
            //alert("delete: "+ id)
            $.ajax({ cache: false,
                url: ajaxUrl,
                type: ajaxType,
                data: {
                    type: "deleteEntry",
                    id: id }
            }).done(function (res) {
                let resJSON = JSON.parse(res);

                if (resJSON.status !== "error") {
                    $('#' + id).remove();
                    if ($('.todo-items').length === 0) {
                        $("#todo-item-list").append('<p id="no-entries">Keine Einträge</p>');
                    }


                } else {
                    alert("Fehler beim Löschen.");
                }
            }).fail(function (err) {
                alert("Es ist ein Fehler aufgetreten: " + err.status + " " + err.statusText);
            });

        });
        // DELETE ENDE

        // ------ DELETE ALL REQUEST START ------
        $('#delete-all-btn').on("click", function () {
            $.ajax({
                url: ajaxUrl,
                type: ajaxType,
                data: {
                    type: "delete-all"
                }
            }).done(function(res) {
                let resJSON = JSON.parse(res);

                if (resJSON.status !== "error") {
                    $('#todo-item-list').empty();
                    $("#todo-item-list").append('<p id="no-entries">Keine Einträge</p>');
                }
            }).fail(function(err) {
                alert("Es ist ein Fehler aufgetreten: " + err.status + " " + err.statusText);
            })
        });
        // DELETE ALL ENDE

        // ------ LOGOUT REQUEST START ------
        $('#logout-btn').on("click", function () {
            $.ajax({
                    url: ajaxUrl,
                    type: ajaxType,
                    data: {
                        type: "logout"
                    }
                }).done(function (res) {
                    let resJSON = JSON.parse(res);
                    if (resJSON.status === "success") {
                        location.reload();
                    } else {
                        alert("Du konntest nicht ausgeloggt werden.");
                    }
            })

        });
        // LOGOUT ENDE
    });

</script>
</body>
</html>



































