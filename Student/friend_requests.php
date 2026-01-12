<div class="friend-requests">
    <h4>Requests</h4>
    <?php if ($requestsResult->num_rows > 0): ?>
        <?php while ($request = $requestsResult->fetch_assoc()): ?>
            <div class="request" id="request-<?php echo $request['student_id']; ?>">
                <div class="info">
                    <div class="profile-pic">
                        <img src="php/images/<?php echo htmlspecialchars($request['image'] ?? 'default.jpg'); ?>" alt="Profile Picture">
                    </div>
                    <div>
                        <h5><?php echo htmlspecialchars($request['firstName'] . ' ' . $request['lastName']); ?></h5>
                        <p class="text-muted"><?php echo $request['mutual_friends']; ?> mutual friends</p>
                    </div>
                </div>
                <div class="action">
                    <form class="accept-form" data-request-id="<?php echo $request['student_id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($request['student_id']); ?>">
                        <input type="hidden" name="action" value="accept">
                        <button type="submit" class="btn btn-primary accept-button">Accept</button>
                    </form>
                    <form class="decline-form" data-request-id="<?php echo $request['student_id']; ?>">
                        <input type="hidden" name="user_id" value="<?php echo htmlspecialchars($request['student_id']); ?>">
                        <input type="hidden" name="action" value="decline">
                        <button type="submit" class="btn btn-primary decline-button">Decline</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No friend requests.</p>
    <?php endif; ?>
    <div class="view_request">
    <a href="Student/friends.php?view=requests">All Requests</a>
</div>
</div>

<div class="friend-requests">
    <h4>Requests</h4>
</div>

<div class="group-join-requests">
    <?php if ($groupRequestsResult->num_rows > 0): ?>
        <?php while ($request = $groupRequestsResult->fetch_assoc()): ?>
            <div class="request" id="group-request-<?php echo $request['request_id']; ?>">
                <div class="info">
                    <h5><?php echo htmlspecialchars($request['firstName'] . ' ' . $request['lastName']); ?> wants to join <?php echo htmlspecialchars($request['group_name']); ?></h5>
                </div>
                <div class="action">
                    <form class="group-accept-form" data-request-id="<?php echo $request['request_id']; ?>" data-group-id="<?php echo $request['group_id']; ?>">
                        <input type="hidden" name="request_type" value="group_join">
                        <input type="hidden" name="action" value="accept">
                        <input type="hidden" name="group_id" value="<?php echo $request['group_id']; ?>">
                        <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                        <button type="submit" class="btn btn-primary accept-button">Accept</button>
                    </form>
                    <form class="group-decline-form" data-request-id="<?php echo $request['request_id']; ?>" data-group-id="<?php echo $request['group_id']; ?>">
                        <input type="hidden" name="request_type" value="group_join">
                        <input type="hidden" name="action" value="decline">
                        <input type="hidden" name="group_id" value="<?php echo $request['group_id']; ?>">
                        <input type="hidden" name="request_id" value="<?php echo $request['request_id']; ?>">
                        <button type="submit" class="btn btn-danger decline-button">Decline</button>
                    </form>
                </div>
            </div>
        <?php endwhile; ?>
    <?php else: ?>
        <p>No group join requests.</p>
    <?php endif; ?>
</div>

<script>
document.querySelectorAll('.accept-form, .decline-form').forEach(function(form) {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const requestId = form.getAttribute('data-request-id');
        const action = form.querySelector('input[name="action"]').value;
        const button = form.querySelector(`.${action}-button`);

        fetch('', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (action === 'accept') {
                    button.textContent = 'Accepted';
                    setTimeout(function() {
                        location.reload(); // Reload the page to fetch and display the next requests
                    }, 3000);
                } else if (action === 'decline') {
                    button.textContent = 'Declined';
                    setTimeout(function() {
                        // Remove the declined request from the DOM
                        const requestDiv = document.getElementById(`request-${requestId}`);
                        if (requestDiv) {
                            requestDiv.remove();
                        }
                        location.reload();
                    }, 3000);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

document.querySelectorAll('.group-accept-form, .group-decline-form').forEach(function(form) {
    form.addEventListener('submit', function(event) {
        event.preventDefault();
        const requestId = form.getAttribute('data-request-id');
        const action = form.querySelector('input[name="action"]').value;
        const button = form.querySelector(`.${action}-button`);

        fetch('', {
            method: 'POST',
            body: new FormData(form)
        })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                if (action === 'accept') {
                    button.textContent = 'Accepted';
                    setTimeout(function() {
                        location.reload(); // Reload the page to fetch and display the next requests
                    }, 3000);
                } else if (action === 'decline') {
                    button.textContent = 'Declined';
                    setTimeout(function() {
                        const requestDiv = document.getElementById(`group-request-${requestId}`);
                        if (requestDiv) {
                            requestDiv.remove();
                        }
                        location.reload();
                    }, 3000);
                }
            }
        })
        .catch(error => console.error('Error:', error));
    });
});

function loadNextRequest() {
    fetch('php/fetch_next_request.php') // This should return the next request or an empty response if none
    .then(response => response.text())
    .then(html => {
        if (html) {
            document.querySelector('.friend-requests').innerHTML += html;
        } else {
            if (document.querySelector('.friend-requests').children.length === 1) {
                document.querySelector('.friend-requests').innerHTML = '<p>No friend requests.</p>';
            }
        }
    });
}
</script>