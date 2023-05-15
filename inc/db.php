<?php
session_start();

class DB
{
    //_____ CLASS VARIABLES____
    private const HOST = "localhost";
    private const DBNAME = "todo";
    private const USER = "root";
    private const PASSWORD = "";

    private static $DBConnection;

    //_____ CLASS METHODS_____
    public static function connectDB(): PDO
    {

        if (self::$DBConnection === null) {
            self::$DBConnection = new PDO('mysql:host=' . self::HOST . ';dbname=' . self::DBNAME . ';charset=utf8', self::USER, self::PASSWORD);

            self::$DBConnection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            self::$DBConnection->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        }
        return self::$DBConnection;
    }
}
//_____ AJAX REQUESTS_____
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    //----- LOGIN REQUEST START -----
    if ($_POST['type'] == 'login') {
        if (isset($_POST['benutzer']) || isset($_POST['password'])) {
            $benutzer = $_POST['benutzer'];
            $password = $_POST['password'];

            $db = DB::connectDB();
            $query = $db->prepare('SELECT * FROM user_table WHERE name = :benutzer');
            $query->bindParam(':benutzer', $benutzer);
            $query->execute();
            $benutzer = $query->fetch(PDO::FETCH_ASSOC);

            $id = $benutzer['id'];

            if ($benutzer !== false && $benutzer['password'] === $password) {
                $_SESSION['userid'] = $id;
                echo json_encode(array(
                    "status" => "success",
                    "userID" => $id
                ));

            } else {
                echo json_encode(array("status" => "error"));
            }
            exit();
        }
    //----- LOGIN REQUEST ENDE -----

    //----- SELECT ENTRIES REQUEST START -----

    } elseif ($_POST['type'] === 'select') {
        $userId = $_SESSION['userid'];

        $db = DB::connectDB();
        $query = $db->prepare('SELECT * from todo_table WHERE UserId = :id');
        $query->bindParam(":id", $userId);
        $query->execute();
        $result = $query->fetchAll(PDO::FETCH_ASSOC);

        echo json_encode($result);
    }
    //----- SELECT ENTRIES REQUEST ENDE -----

    //----- INSERT REQUEST START -----
    elseif ($_POST['type'] == 'new-todo') {

        if (empty($_POST["todo"])) {
            echo json_encode(array("status" => "error"));
            exit();
        }

        $todo = $_POST["todo"];
        date_default_timezone_set("Europe/Zurich");
        $datum = date('Y-m-d');
        $userId = $_SESSION['userid'];
        try {

            require_once('db.php');
            $db = DB::connectDB();
            $query = $db->prepare('INSERT INTO todo_table (todo, Datum, UserId) VALUES (:todo,:datum,:id)');
            $query->bindParam(":todo", $todo);
            $query->bindParam(":id", $userId);
            $query->bindParam(":datum", $datum);
            $query->execute();

            $lastID = $db->lastInsertId();


        } catch (PDOException $ex) {
            echo json_encode(array("status" => "error"));
            exit();
        }

        echo json_encode(array(
                "ID" => $lastID,
                "Datum" => $datum,
                "todo" => $todo)
        );
        exit();

    } elseif ($_POST['type'] == 'deleteEntry') {
        if (empty($_POST["id"])) {
            echo json_encode(array("status" => "error"));
            exit();
        }
        $id = $_POST["id"];

        try {
            require_once('db.php');
            $db = DB::connectDB();
            $query = $db->prepare('DELETE FROM todo_table WHERE ID = :id');
            $query->bindParam(":id", $id, PDO::PARAM_INT);
            $query->execute();

        } catch (PDOException $ex) {
            echo json_encode(array("status" => "error"));
            exit();
        }
        echo json_encode(array(
                "status" => "success")
        );
        exit();

    }
    //----- INSERT REQUEST ENDE -----

    //----- DELETE ALL REQUEST START -----
    elseif ($_POST['type'] == 'delete-all') {

        $userId = $_SESSION['userid'];
        try {
            require_once('db.php');
            $db = DB::connectDB();
            $query = $db->prepare('DELETE FROM todo_table WHERE UserId = :id');
            $query->bindParam(":id", $userId, PDO::PARAM_INT);
            $query->execute();


        } catch (PDOException $ex) {
            echo json_encode(array("status" => "error"));
            exit();
        }


        echo json_encode(array(
                "status" => "success")
        );
        exit();

    }
    //----- DELETE ALL REQUEST ENDE -----

    //----- LOGOUT REQUEST START -----
    elseif ($_POST['type'] == 'logout') {

        session_destroy();
        echo json_encode(array("status" => "success"));
        exit();

    }
    //----- LOGOUT REQUEST ENDE -----

    exit();
}
