<?php
session_start();
if (isset($_SESSION['user_id'], $_SESSION['username'], $_SESSION['fav_genre'])) {
	$user_id = $_SESSION['user_id'];
	$username = $_SESSION['username'];
	$fav_genre = $_SESSION['fav_genre'];
	$friends = $_SESSION['friends'];
	$outgoing_friend_requests = $_SESSION['outgoing_friend_requests'];
	$incoming_friend_requests = $_SESSION['incoming_friend_requests'];
} else {
    header('Location: signin.php');
    exit();
}
?>

<!DOCTYPE html>
<html>
<head>
	<title>Friends</title>
</head>
<body>
	<header>
		<h1>Friends</h1>
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
            <h2>Search Users</h2>
			<?php
			if (!empty($user_id)) {
				echo '
				<form method="post" action="publish_users.php">
					<input type="hidden" name="user_id" value="' . $user_id . '">
					<input type="text" name="searchQuery" placeholder="Search users..." required>
                	<input type="submit" name="search" value="Search">
            	</form> <br>
				';
			}
			?>
    </section>

	<section>
		<h2>Friends</h2>
		<?php
		if (!empty($friends)) {
			echo "<table>";
		    echo "<tr><th>Username</th></tr>";
		    foreach ($friends as $friend) {
		        echo "<tr>";
		        echo "<td>" . $friend['username'] . "</td>";
				echo "<td> <button class='remove' action='remove' user1id='" . $user_id . "' user2id='" . $friend['user_id'] . "'>Remove</button> </td>";
		        echo "</tr>";
		    }
		    echo "</table>";
		} else {
			echo "<p>No friends found.</p>";
		}
		?>
	</section>

	<section>
		<h2>Outgoing Friend Requests</h2>
		<?php
		if (!empty($outgoing_friend_requests)) {
			echo "<table>";
		    echo "<tr><th>Username</th><th>Action</th></tr>";
		    foreach ($outgoing_friend_requests as $user) {
		        echo "<tr>";
		        echo "<td>" . $user['username'] . "</td>";
				echo "<td> <button class='delete' action='delete' senderid='" . $user_id . "' receiverid='" . $user['receiver_id'] . "'>Delete</button> </td>";
		        echo "</tr>";
		    }
		    echo "</table>";
		} else {
			echo "<p>No outgoing friend requests found</p>";
		}
		?>
	</section>

	<section>
		<h2>Incoming Friend Requests</h2>
		<?php
		if (!empty($incoming_friend_requests)) {
			echo "<table>";
		    echo "<tr><th>Username</th><th>Action</th></tr>";
		    foreach ($incoming_friend_requests as $user) {
		        echo "<tr>";
		        echo "<td>" . $user['username'] . "</td>";
				echo "<td> <button class='accept' action='accept' senderid='" . $user['sender_id'] . "' receiverid='" . $user_id . "'>Accept</button>  <button class='decline' action='decline' senderid='" . $user['sender_id'] . "' receiverid='" . $user_id . "'>Decline</button></td>";
		        echo "</tr>";
		    }
		    echo "</table>";
		} else {
			echo "<p>No incoming friend requests found</p>";
		}
		?>
	</section>
	</main>

	<script>
    document.addEventListener('click', function (event) {
        if (event.target && event.target.className === 'delete') {
			const action = event.target.getAttribute('action');
            const senderId = event.target.getAttribute('senderid');
            const receiverId = event.target.getAttribute('receiverid');
            const data = {
				action: action,
                sender_id: senderId,
                receiver_id: receiverId
            };
            const jsonData = JSON.stringify(data);

            fetch('publish_friend.php', {
                method: 'POST',
                body: jsonData,
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (response.status === 200) {
                    alert('Friend request deleted');
                } else if (response.status === 400) {
                    alert('Deleting friend request failed')
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
        } else if (event.target && event.target.className === 'accept') {
			const action = event.target.getAttribute('action');
            const senderId = event.target.getAttribute('senderid');
            const receiverId = event.target.getAttribute('receiverid');
            const data = {
				action: action,
                sender_id: senderId,
                receiver_id: receiverId
            };
            const jsonData = JSON.stringify(data);

            fetch('publish_friend.php', {
                method: 'POST',
                body: jsonData,
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (response.status === 200) {
                    alert('Friend request accepted');
                } else if (response.status === 400) {
                    alert('Accepting friend request failed')
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
		} else if (event.target && event.target.className === 'decline') {
			const action = event.target.getAttribute('action');
            const senderId = event.target.getAttribute('senderid');
            const receiverId = event.target.getAttribute('receiverid');
            const data = {
				action: action,
                sender_id: senderId,
                receiver_id: receiverId
            };
            const jsonData = JSON.stringify(data);

            fetch('publish_friend.php', {
                method: 'POST',
                body: jsonData,
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (response.status === 200) {
                    alert('Friend request declined');
                } else if (response.status === 400) {
                    alert('Declining friend request failed')
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
		} else if (event.target && event.target.className === 'remove') {
			const action = event.target.getAttribute('action');
            const user1Id = event.target.getAttribute('user1id');
            const user2Id = event.target.getAttribute('user2id');
            const data = {
				action: action,
                user1_id: user1Id,
                user2_id: user2Id
            };
            const jsonData = JSON.stringify(data);

            fetch('publish_friend.php', {
                method: 'POST',
                body: jsonData,
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => {
                if (response.status === 200) {
                    alert('Friend removed');
                } else if (response.status === 400) {
                    alert('Removing friend failed')
                }
            })
            .catch(error => {
                console.error('Error:', error);
            });
		}
    });
    </script>

	<footer>
	   <form method="post" action="publish_signout.php">
                <input type="submit" name="signout" value="Sign Out">
        </form>
	</footer>
</body>
</html>