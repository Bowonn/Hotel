function get_booking() {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/new_booking.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function () {
        document.getElementById('table-data').innerHTML = this.responseText;
    }

    xhr.send('get_booking');
}

function delete_booking(id) {
    if (confirm("Are you sure, you want to remove this user?")) {
        let data = new FormData();
        data.append('id', id);
        data.append('delete_booking', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/new_booking.php", true);

        xhr.onload = function () {

            if (this.responseText == 1) {
                alert('success', 'Booking Removed!');
                get_users();
            } else {
                alert('error', 'Booking removal failed!');
            }
        }
        xhr.send(data);
    }
}

function search_booking(query) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/new_bookings.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function () {
        let tableData = document.getElementById('table-data');
        if (tableData) {
            tableData.innerHTML = this.responseText;
        } else {
            console.error('Element with ID "table-data" not found.');
        }
    }

    xhr.send('search_booking&query=' + query);
}

window.onload = function () {
    get_bookings();
}


window.onload = function () {
    get_bookings();
}


function toggle_status(id, val) {
    let xhr = new XMLHttpRequest();
    xhr.open("POST", "ajax/users.php", true);
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');

    xhr.onload = function () {
        if (this.responseText == 1) {
            alert('success', 'Status toggle!');
            get_users()
        } else {
            alert('error', 'Server Down!');
        }
    }

    xhr.send('toggle_status=' + id + '&value=' + val);
}

function remove_user(user_id) {
    if (confirm("Are you sure, you want to remove this user?")) {
        let data = new FormData();
        data.append('user_id', user_id);
        data.append('remove_user', '');

        let xhr = new XMLHttpRequest();
        xhr.open("POST", "ajax/users.php", true);

        xhr.onload = function () {

            if (this.responseText == 1) {
                alert('success', 'User Removed!');
                get_users();
            } else {
                alert('error', 'User removal failed!');
            }
        }
        xhr.send(data);

    }
}



window.onload = function () {
    get_booking();
}