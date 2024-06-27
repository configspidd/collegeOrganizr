<?php
class User {
    private string $username;
    private string $eMail;
    private string $password;

    public function __construct($username, $eMail, $password) {
        $this->username = htmlspecialchars($username, ENT_QUOTES, 'UTF-8');
        $this->eMail = filter_var($eMail, FILTER_SANITIZE_EMAIL);
        $this->password = $password;
    }

    public function getEMail(): string {
        return $this->eMail;
    }

    public function getUsername(): string {
        return $this->username;
    }

    public function getPassword(): string {
        return $this->password;
    }

    public function saveToDatabase($servername, $dbname, $dbusername, $dbpassword) {
        try {
            $conn = new PDO("mysql:host=$servername;dbname=$dbname", $dbusername, $dbpassword);
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            $stmt = $conn->prepare("INSERT INTO users (username, email, password) VALUES (:username, :email, :password)");

            $hashedPassword = password_hash($this->password, PASSWORD_DEFAULT);

            $stmt->bindParam(':username', $this->username);
            $stmt->bindParam(':email', $this->eMail);
            $stmt->bindParam(':password', $hashedPassword);

            $stmt->execute();

            // Return the last inserted ID
            return $conn->lastInsertId();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }
}
?>