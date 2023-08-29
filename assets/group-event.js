/*
 * Welcome to your app's main JavaScript file!
 *
 * We recommend including the built version of this JavaScript file
 * (and its CSS file) in your base layout (base.html.twig).
 */

// any CSS you import will output into a single css file (app.css in this case)
import './styles/group-event.scss';

// start the Stimulus application
import LeaderLine from 'leader-line-new';

document.addEventListener('DOMContentLoaded', function () {
    let optionsPanel = document.querySelector('.options-panel .user-list');
    let selectedPanel = document.querySelector('.selected-panel .user-list');
    let selectedUsersField = document.querySelector('#init_group_event_selectedUsers');

    if (optionsPanel) {
        optionsPanel.addEventListener('click', function (event) {
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

    if (selectedPanel) {
        selectedPanel.addEventListener('click', function (event) {
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
document.addEventListener('DOMContentLoaded', function () {
    const eventGroups = document.querySelectorAll('.event-group');
    eventGroups.forEach(function (group) {
        const usersContainer = group.nextElementSibling;
        const userTiles = usersContainer.querySelectorAll('.user-tile');
        userTiles.forEach(function (userTile) {
            userTile.classList.add('collapsed');
        });

        group.addEventListener('click', function () {
            group.classList.toggle('collapsed');
            usersContainer.classList.toggle('collapsed');
        });
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const container = document.querySelector('.event-header-container');
    if (container) {
        container.classList.add('collapsed');

        const header = document.querySelector('.event-header-user-list');
        header.addEventListener('click', function () {
            container.classList.toggle('collapsed');
        });
    }
});


// payment adding
document.addEventListener("DOMContentLoaded", function () {
    const tiles = document.querySelectorAll(".event-payment-form-pool .event-payment-form-tile");
    const selection = document.querySelector(".event-payment-form-selection");
    const groupMembers = document.querySelector(".event-payment-form-group-members");
    let formField = document.querySelector("#create_event_payment_debtors");
    let selectedTile = null;

    tiles.forEach(tile => {
        tile.addEventListener("click", function () {
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
            if (members) {
                members = members.split(',');
            }

            groupMembers.innerHTML = '';
            if (members) {
                members.forEach(function (member) {
                    let memberDiv = document.createElement('div');
                    memberDiv.textContent = member;
                    memberDiv.classList.add('event-user-tile');
                    groupMembers.appendChild(memberDiv);
                });
            } else {
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


// function packTiles(containerWidth, containerHeight, tiles) {
//     const packedPositions = [];
//     const tileSize = 100; // Assuming all tiles have the same size
//
//     tiles.forEach((tile) => {
//         let position;
//         do {
//             position = {
//                 x: Math.random() * (containerWidth - tileSize),
//                 y: Math.random() * (containerHeight - tileSize),
//             };
//         } while (checkCollisions(position, packedPositions, tileSize));
//
//         packedPositions.push(position);
//         tile.style.left = `${position.x}px`;
//         tile.style.top = `${position.y}px`;
//     });
// }
//
// function checkCollisions(position, packedPositions, tileSize) {
//     for (const otherPosition of packedPositions) {
//         if (
//             position.x < otherPosition.x + tileSize &&
//             position.x + tileSize > otherPosition.x &&
//             position.y < otherPosition.y + tileSize &&
//             position.y + tileSize > otherPosition.y
//         ) {
//             return true; // Collision detected
//         }
//     }
//     return false; // No collision
// }
//
// window.addEventListener("DOMContentLoaded", () => {
//     const container = document.querySelector(".container");
//     const tiles = Array.from(document.querySelectorAll(".tile"));
//     const containerWidth = container.clientWidth;
//     const containerHeight = container.clientHeight;
//
//     packTiles(containerWidth, containerHeight, tiles);
//
//     drawLine(document.getElementById('user-1'), document.getElementById('user-2'));
//     drawLine(document.getElementById('user-1'), document.getElementById('user-3'));
//     drawLine(document.getElementById('user-1'), document.getElementById('user-5'));
// });


// const tiles = document.querySelectorAll('.user-tile');
//
// // Create an empty object to store the tile IDs as keys and 0 as values
// const tileValues = {};
//
// // Iterate over the tiles and set 0 as the value for each tile ID
// tiles.forEach(tile => {
//     const tileId = tile.id;
//     tileValues[tileId] = 0;
// });
//
// const startAnchorsLeft = {0: 'right', 1: 'top', 2: 'bottom', 3: 'left'};
// const endAnchorsRight = {0: 'left', 1: 'bottom', 2: 'top', 3: 'right'};
//
// const endAnchorsLeft = {0: 'left', 1: 'top', 2: 'bottom', 3: 'right'};
// const startAnchorsRight = {0: 'left', 1: 'bottom', 2: 'top', 3: 'right'};
//
// drawLine(document.getElementById('user-2'), document.getElementById('user-3'));
//
// function drawLine(element1, element2) {
//     let startPoint = 'left';
//     let endPoint = 'left';
//
//     if (element1.classList.contains('c1')){
//         startPoint = startAnchorsLeft[tileValues[element1.id]];
//         startPoint = 'right';
//     }
//     if (element1.classList.contains('c3')){
//         startPoint = startAnchorsRight[tileValues[element1.id]];
//     }
//
//     if (element2.classList.contains('c1')){
//         endPoint = endAnchorsLeft[tileValues[element2.id]];
//     }
//     if (element2.classList.contains('c3')){
//         endPoint = endAnchorsRight[tileValues[element2.id]];
//     }
//
//     console.log(startPoint, endPoint);
//     let line = new LeaderLine(
//         element1,
//         element2,
//         {endLabel: LeaderLine.pathLabel('23€'),}
//     );
//
//     line.setOptions({startSocket: startPoint, endSocket: endPoint});
//     line.path = 'auto';
//     line.color = getRandomColor();
//
//     tileValues[element1.id] = tileValues[element1.id]+1;
//     tileValues[element2.id] = tileValues[element2.id]+1;
// }

let users = document.querySelectorAll('.ge-result-user-container');
users.forEach(user => {
    let userName = user.querySelector('.corner')

    let transactions = user.querySelectorAll('.ge-result-pair-container');
    transactions.forEach((transaction, index) => {
        let rows = transaction.querySelectorAll('.ge-result-transaction-pair');
        let tiles = transaction.querySelectorAll('.ge-result-tile');

        if (index > 0) {

            if (rows[0].classList.contains('ge-result-tile-invisible')){
                // => row
                console.log(tiles[4].innerHTML);
                tiles[1].innerHTML = tiles[4].innerHTML;
                tiles[1].classList.toggle('reverse');
                rows[1].classList.toggle('hidden');

                // <=
                drawLine(tiles[2], tiles[1], 'left', 'right', 'magnet', '#8bb976', 'behind');
                drawBackLine(tiles[1], userName, 'left', 'bottom', 'magnet', 20);
            }else{
                // =>
                drawLine(userName, tiles[1], 'bottom', 'left', 'magnet', '#a86464', 'behind');
                drawLine(tiles[1], tiles[2], 'right', 'left', 'straight', '#a86464');
            }

            if (rows[1].classList.contains('ge-result-tile-invisible')){
                rows[1].classList.toggle('hidden');
            }else{
                // <=
                if (!rows[0].classList.contains('ge-result-tile-invisible')) {
                    drawLine(tiles[2], tiles[4], 'bottom', 'right', 'arc', '#8bb976', 'behind');
                    drawBackLine(tiles[4], userName, 'left', 'bottom', 'magnet', 20);
                }
            }
        }
    });
});

function drawLine(element1, element2, startPoint, endPoint, style, color, end) {
    let line = new LeaderLine(
        element1,
        element2,
        {dash: {animation: true}}
    );

    line.setOptions({startSocket: startPoint, endSocket: endPoint});
    line.path = style;
    line.color = color;
    line.endPlug = end;
}

function drawBackLine(element1, element2, startPoint, endPoint, style, xTarget) {
    let line = new LeaderLine(
        element1,
        LeaderLine.pointAnchor(element2, {
            x: xTarget,
            y: 45
        }),
        {dash: {animation: true}}
    );

    line.setOptions({startSocket: startPoint, endSocket: endPoint});
    line.path = style;
    line.color = '#8bb976';
}

function getRandomColor() {
    const letters = '0123456789ABCDEF';
    let color = '#';
    for (let i = 0; i < 6; i++) {
        color += letters[Math.floor(Math.random() * 16)];
    }
    return color;
}



let scrollContainer = document.querySelector('.scroll-container');
if (scrollContainer){
    document.addEventListener('DOMContentLoaded', function () {
        adjustArrowLength();
    });

    function adjustArrowLength() {
        const slideArrows = document.querySelectorAll('.slide-arrow');
        slideArrows.forEach(slideArrow => {
            const arrowContainer = slideArrow.querySelector('.arrow-container');
            const slideName = slideArrow.querySelector('.slide-name');

            const textWidth = slideName.offsetWidth + 8;
            arrowContainer.style.width = `${textWidth}px`;
        });
    }


    const dataMaxValue = scrollContainer.dataset.max;

    // Add swipe event listener to the body or a specific container
    document.body.addEventListener('touchstart', handleTouchStart, false);
    document.body.addEventListener('touchmove', handleTouchMove, false);

    let xStart = null;
    const sensitivity = 50; // Minimum distance (in pixels) to detect a swipe

    function handleTouchStart(event) {
        xStart = event.touches[0].clientX;
    }

    function handleTouchMove(event) {
        if (!xStart) return;

        const xEnd = event.touches[0].clientX;
        const xDiff = xStart - xEnd;

        if (Math.abs(xDiff) > sensitivity) {
            // Swipe detected, handle swipe direction
            if (xDiff > 0) {
                // Swipe left, navigate to the next slide
                navigateToSlide('next');
            } else {
                // Swipe right, navigate to the previous slide
                navigateToSlide('previous');
            }

            xStart = null;
        }
    }

    function navigateToSlide(direction) {
        const urlParams = new URLSearchParams(window.location.search);
        const currentSlide = parseInt(urlParams.get('slide')); // Get the current slide value from the URL

        let newSlide;
        if (direction === 'next') {
            newSlide = currentSlide + 1;
        } else {
            newSlide = currentSlide - 1;
        }

        // Handle slide boundaries (e.g., prevent negative slides)
        if (newSlide >= 0 && newSlide <= dataMaxValue) {
            urlParams.set('slide', newSlide); // Update the slide parameter with the new value
            const newUrl = `${window.location.pathname}?${urlParams.toString()}`;
            window.location.href = newUrl;
        }
    }
}



/*
        <div id="camera-container">
            <button id="activate-camera-btn">Activate Camera</button>
            <video id="camera-stream" autoplay></video>
            <button id="capture-btn" disabled>Take Picture</button>
        </div>

         #camera-container {
             display: flex;
             flex-direction: column;
             align-items: center;
         }
        #camera-stream {
            width: 100%;
            max-width: 400px;
            height: auto;
        }

        // Variables to store references to DOM elements
const activateCameraBtn = document.getElementById('activate-camera-btn');
const captureBtn = document.getElementById('capture-btn');
const cameraStream = document.getElementById('camera-stream');
let stream;

// Function to start the camera stream
async function activateCamera() {
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: true });
        cameraStream.srcObject = stream;
        activateCameraBtn.disabled = true;
        captureBtn.disabled = false;
    } catch (error) {
        console.error('Error accessing the camera:', error);
    }
}

// Function to take a picture
function takePicture() {
    const canvas = document.createElement('canvas');
    canvas.width = cameraStream.videoWidth;
    canvas.height = cameraStream.videoHeight;
    const context = canvas.getContext('2d');
    context.drawImage(cameraStream, 0, 0, canvas.width, canvas.height);
    const image = canvas.toDataURL('image/jpeg'); // You can change the image type here (e.g., 'image/jpeg')
    console.log(image);
    // You can now do something with the image, like displaying it on the page or sending it to a server.
}

// Event listeners
activateCameraBtn.addEventListener('click', activateCamera);
captureBtn.addEventListener('click', takePicture);

 */





