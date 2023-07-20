/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/group-event.scss';

// start the Stimulus application
import './bootstrap';

document.addEventListener('DOMContentLoaded', function() {
    let optionsPanel = document.querySelector('.options-panel .user-list');
    let selectedPanel = document.querySelector('.selected-panel .user-list');
    let selectedUsersField = document.querySelector('#init_group_event_selectedUsers');

    if (optionsPanel){
        optionsPanel.addEventListener('click', function(event) {
            if (event.target.classList.contains('user-option')) {
                let value = event.target.getAttribute('data-value');
                let label = event.target.innerText;
                let selectedUser = document.createElement('li');
                selectedUser.classList.add('selected-user');
                selectedUser.setAttribute('data-value', value);
                selectedUser.innerText = label;
                selectedPanel.appendChild(selectedUser);
                event.target.remove();
                updateSelectedUsersField();
            }
        });
    }

    if (selectedPanel){
        selectedPanel.addEventListener('click', function(event) {
            if (event.target.classList.contains('selected-user')) {
                let value = event.target.getAttribute('data-value');
                let label = event.target.innerText;
                let userOption = document.createElement('li');
                userOption.classList.add('user-option');
                userOption.setAttribute('data-value', value);
                userOption.innerText = label;
                optionsPanel.appendChild(userOption);
                event.target.remove();
                updateSelectedUsersField();
            }
        });
    }

    function updateSelectedUsersField() {
        let selectedUsers = document.querySelectorAll('.selected-user');
        let selectedUserValues = Array.from(selectedUsers).map(function (user) {
            return user.getAttribute('data-value');
        });
        selectedUsersField.value = selectedUserValues.join(',');
    }
});

//popover
document.addEventListener('DOMContentLoaded', function() {
    const eventGroups = document.querySelectorAll('.event-group');
    eventGroups.forEach(function(group) {
        const usersContainer = group.nextElementSibling;
        const userTiles = usersContainer.querySelectorAll('.user-tile');
        userTiles.forEach(function(userTile) {
            userTile.classList.add('collapsed');
        });

        group.addEventListener('click', function() {
            group.classList.toggle('collapsed');
            usersContainer.classList.toggle('collapsed');
        });
    });
});


document.addEventListener('DOMContentLoaded', function() {
    const container = document.querySelector('.event-header-container');
    if (container){
        container.classList.add('collapsed');

        const header = document.querySelector('.event-header-user-list');
        header.addEventListener('click', function() {
            container.classList.toggle('collapsed');
        });
    }
});


// payment adding
document.addEventListener("DOMContentLoaded", function() {
    const tiles = document.querySelectorAll(".event-payment-form-pool .event-payment-form-tile");
    const selection = document.querySelector(".event-payment-form-selection");
    const groupMembers = document.querySelector(".event-payment-form-group-members");
    let formField = document.querySelector("#create_event_payment_debtors");
    let selectedTile = null;

    tiles.forEach(tile => {
        tile.addEventListener("click", function() {
            if (selectedTile) {
                // Move the previously selected tile back to the pool
                selectedTile.classList.remove("event-payment-form-selected");
                const pool = document.querySelector(".event-payment-form-pool");
                pool.appendChild(selectedTile);
            }

            formField.value = tile.getAttribute('groupId');

            // Move the clicked tile to the selection field
            tile.classList.add("event-payment-form-selected");
            selection.innerHTML = "";
            selection.appendChild(tile.cloneNode(true));

            // Remove the clicked tile from the pool
            tile.parentNode.removeChild(tile);

            // Show the group members in the corresponding field
            let members = tile.getAttribute('members');
            if (members){
                members = members.split(',');
            }

            groupMembers.innerHTML = '';
            if (members){
                members.forEach(function(member) {
                    let memberDiv = document.createElement('div');
                    memberDiv.textContent = member;
                    memberDiv.classList.add('event-user-tile');
                    groupMembers.appendChild(memberDiv);
                });
            }else{
                let memberDiv = document.createElement('div');
                memberDiv.textContent = tile.textContent;
                memberDiv.classList.add('event-user-tile');
                groupMembers.appendChild(memberDiv);
            }


            // groupMembers.textContent = 'a';

            selectedTile = tile;
        });
    });
});









