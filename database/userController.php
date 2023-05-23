<?php

//php UsersServ calss
class userController
{
    public $db = null;

    public function __construct(DBController $db)
    {
        if (!isset($db->con))
            return null;
        $this->db = $db;
    }

    //insert into user table ( insert )

    public function insertintoUser($params = null, $table = "users")
    {
        if ($this->db->con != null) {
            if ($params != null) {
                //create sql query
                $folder_name = abs(crc32(uniqid()));
                $query_string = sprintf("INSERT INTO %s(username, email, password, file_size, file_lim, folder_name) 
                VALUES('%s','%s','%s','%s','%d','%s')", $table, $params["username"], $params["email"], password_hash($params["password"], PASSWORD_BCRYPT), $params["file_limit"], $params["file_size"], $folder_name);
                mkdir($_SERVER['DOCUMENT_ROOT'] . '/pages/folders' . '/' . $folder_name);

                //execute query
                $result = $this->db->con->query($query_string);
                echo "<script>" . "alert('Registeration completed successfully.');" . "</script>";
                echo "<script>" . "window.location.href='./users.php';" . "</script>";
                return $result;
            }
        }
    }
    //login function
    public function identification($params = null, $table = 'users')
    {

        if ($this->db->con != null) {

            $query_string = sprintf("SELECT * FROM %s WHERE username = '%s'", $table, $params["username"]);

            if ($params != null) {
                $result = $this->db->con->query($query_string);
                $queryResult = mysqli_fetch_array($result, MYSQLI_ASSOC);
                $password = $queryResult['password'];

                if (password_verify($params['password'], $password)) {
                    //variables
                    $_SESSION['username'] = $queryResult['username'];
                    $_SESSION['email'] = $queryResult['email'];
                    $_SESSION['folder_name'] = $queryResult['folder_name'];
                    $_SESSION['file_lim'] = $queryResult['file_lim'];
                    $_SESSION['file_size'] = $queryResult['file_size'];
                    $_SESSION["loggedin"] = TRUE;
                    $_SESSION['id'] = $queryResult['id'];

                    //admin or normal user
                    if ($_SESSION['username'] == "admin") {
                        echo "<script>" . "window.location.href='./users.php'" . "</script>";
                    } else {
                        echo "<script>" . "window.location.href='./files.php'" . "</script>";
                    }
                    die;
                } else {
                    //wrong user name or password
                    header('Location: ./login.php?Invalid=Please enter Coerrect user name and password');
                    die;
                }
            }
        }
    }

    public function chackPassword($id = null , $password = null, $table = 'users'){
        if (isset($id) && isset($password)) {

            $result = $this->db->con->query("SELECT password FROM {$table} WHERE id  = '{$id}'");

            if ($result != false) {
                $realpassword = mysqli_fetch_array($result, MYSQLI_ASSOC);
                if (password_verify($password, $realpassword['password'])) {
                    return false;
                }
                else{
                    return true;
                }
            }
        }
    }
    //get all user data
    public function getUsersData($table = 'users')
    {

        $result = $this->db->con->query("SELECT * FROM {$table}");

        $resultArray = array();

        if ($result != false) {
            while ($item = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
                $resultArray[] = $item;
            }
        }

        return $resultArray;
    }
    
    //get specific user data using id
    public function getUsersById($user_id = null, $table = 'users')
    {
        if (isset($user_id)) {
            $result = $this->db->con->query("SELECT * FROM {$table} WHERE id = '{$user_id}'");

            if ($result != false) {
                $resultArray = mysqli_fetch_array($result, MYSQLI_ASSOC);
            }
        }

        return $resultArray;
    }

    //check if username is already used
    public function checkUserName($username = null, $table = 'users')
    {
        if (isset($username)) {
            $result = $this->db->con->query("SELECT id FROM {$table} WHERE username = '{$username}'");
            if ($result != false) {
                $resultArray = mysqli_fetch_array($result, MYSQLI_ASSOC);

                if (isset($resultArray)) {
                    $check = $resultArray['id'];
                } else {
                    $check = 0;
                }

                return $check;
            }
        }
    }

    //check if user email is already used
    public function checkUserEmail($email = null, $table = 'users')
    {
        if (isset($email)) {
            $result = $this->db->con->query("SELECT id FROM {$table} WHERE email = '{$email}'");
            if ($result != false) {
                $resultArray = mysqli_fetch_array($result, MYSQLI_ASSOC);

                if (isset($resultArray)) {
                    $check = $resultArray['id'];
                } else {
                    $check = 0;
                }

                return $check;
            }
        }
    }

    //delete user using id
    public function deleteUser($user_id = null, $table = 'users')
    {
        if ($user_id != null) {

            $result1 = $result = $this->db->con->query("SELECT folder_name FROM users WHERE id ={$user_id}");

            $row = mysqli_fetch_assoc($result1);

            $dirname = $_SERVER['DOCUMENT_ROOT'] . '/pages/folders' . '/' . $row["folder_name"];
            array_map('unlink', glob("$dirname/*.*"));
            rmdir($dirname);

            $result = $this->db->con->query("DELETE FROM {$table} WHERE id={$user_id}");

            if ($result) {
                header("Location:" . $_SERVER['PHP_SELF']);
            }
            return $result;
        }
    }

    //edit user information by admin
    public function updateUser($param_user_id = NULL, $paramusername = NULL, $param_email = NULL, $param_password = NULL, $param_file_size = NULL, $param_file_lim = NULL, $table = 'users')
    {
        if ($param_user_id != null) {

            $result = $this->db->con->query("UPDATE Users SET username = '{$paramusername}' ,email = '{$param_email}' , password = '{$param_password}', file_size = {$param_file_size}, file_lim = {$param_file_lim } WHERE id = {$param_user_id} ");
           
            return $result;
        }
    } 

    //edit user profile 
    public function updateUserPassword($param_user_id = NULL, $param_password = NULL, $table = 'users')
    {
        if ($param_user_id != null && $param_password != NULL) {

            $result = $this->db->con->query("UPDATE {$table} SET password='{$param_password}' WHERE id='{$param_user_id}'");
           
            return $result;
        }
    } 
    //logout
    public function logout()
    {
        $_SESSION = array();
        session_destroy();
        header("Location: " . $_SERVER['DOCUMENT_ROOT'] . "/pages/login.php;'");
        # Unset all session variables

    }
}
