<?php
session_start();
if (isset($_SESSION['user_id'])) {
    $user_id = $_SESSION['user_id'];
}

if (isset($_SESSION['users'])) {
    $users = $_SESSION['users'];
} else {
    header('Location: friends.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>User Results</title>
</head>
<body>
    <header>
        <h1>User Results</h1>
        <nav>
            <ul>
                <li><a href="index.php">Home</a></li>
                <li><a href="recommendations.php">Recommendations</a></li>
                <li><a href="profile.php">Profile</a></li>
                <li><a href="bookmarks.php">Bookmarks</a></li>
                <li><a href="friends.php">Friends</a></li>
            </ul>
        </nav>
    </header>

    <main>
        <section>
            <h2>Here are your user results:</h2>
            <?php
            if (!empty($users)) {
                echo '<ul>';
                foreach ($users as $user) {
                    echo '<li>';
                    echo '<strong>Username:</strong> ' . $user['username'] . '<br>';
                    echo '<button class="friend_request" senderid="' . $user_id . '"receiverid="' . $user['user_id'] . '">Send Friend Request</button>';
                    echo '</li>';
                }
                echo '</ul>';
                unset($_SESSION['users']);
            } else {
                echo 'No results found.';
                unset($_SESSION['users']);
            }
            ?>
        </section>
    </main>

    <script>
    document.addEventListener('click', function (event) {
        if (event.target && event.target.className === 'friend_request') {
            const senderId = event.target.getAttribute('senderid');
            const receiverId = event.target.getAttribute('receiverid');
            const data = {
                sender_id: senderId,
                receiver_id: receiverId
            };
            const jsonData = JSON.stringify(data);

            fetch('publish_friend_request.php', {
                method: 'POST',
                body: jsonData,
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (response.status === 200) {
                    alert('Friend request sent');
                } else if (response.status === 401) {
                    alert('User already has a pending friend request from you');
                } else if (response.status === 403) {
                    alert('User is already your friend');
                } else if (response.status === 400) {
                    alert('Friend request failed');
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        }
    });
    </script>
</body>
</html>