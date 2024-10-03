<x-master-layout>
<style>
    /*inbox css*/
    @media screen and (min-width: 992px) {
        .custom-space {
            max-width: 300px;
        }
    }
    .font-13 {
        font-size: 13px;
        line-height: 20px;
        color: #000000;
    }
    .font-14 {
        font-size: 14px;
        line-height: 21px;
    }
    .text-light {
        color: #f8f9fa !important;
    }
    .fw-500 {
        font-weight: 500
    }
    .grey-clr {
        color: #7E7E7E;
    }
    .bg-darkblue {
        background-color: #5B7FFF;
    }
    .border-bottom-inbox{
        border-bottom: 1px solid #00000033;
    }
    .position-relative {
        position: relative !important;
    }
    .inbox-massage-sec {
        height: 75vh;
    }
    .inbox-massage-content-main {
        overflow: auto;
        /* height: 500px; */
        height: calc(100% - 80px);
    }
    .inbox-massage-content-main .card-body:hover, .card-body.active {
    background: #5F60B9;
    box-shadow: inset 0px -1px 0px rgb(0 0 0 / 20%);
}    .Quick-Chat-sec-main {
        height: 75vh;
    }
    .Quick-middle-sec {
        background: #e5e8f5;
        padding: 20px;
        overflow: auto;
        /* height: 450px; */
        height: calc(100% - 108px);
    }
        .inbox-massage-content-main .card-body:hover .border-bottom-inbox{
        border-color: #5F60B9;
    }
    .inbox-massage-content-main .card-body:hover p{
        color: #ffffff;
    }
    .inbox-massage-content-main .card-body.active p{
        color: #ffffff;
    }
    .inboxwidth{
        width: 44%;
    }
    .inboxwidthtwo{
        width: 56%;
    }
    .Quick-middle-chat {
        padding: 8px 10px;
    }
    .custom-space {
    padding: 8px 68px 8px 10px;
}
    .custom-space{
        padding: 8px 100px 8px 10px;
        font-size: 14px;
        line-height: 21px;
        font-weight: 400;
        background-color: #5B7FFF;
        position: relative !important;
    }
    span.time-sec {
        position: absolute;
        right: 10px;
        bottom: 4px;
    }
    .send-massage-place{
        margin-bottom: 1rem !important;
        justify-content: flex-end !important;
        position: relative !important;
        display: flex !important;
    }
    .send-massage:after {
        position: absolute;
        content: "";
        border: 5px solid #5B7FFF;

        border-left-color: transparent;
        border-bottom-color: transparent;
        bottom: -8px;
        transform: rotate(-5deg);
    }
    .receive-massage:after{
        position: absolute;
        content: "";
        border: 6px solid #ffffff;

        border-right-color: transparent;
        border-top-color: transparent;
        top: -8px;
        transform: rotate(-5deg);
    }
    .form-control.form-control-flush {
        border: 0;
        padding: 15px 20px;
    }
    .Quick-footer-attachfile {
        position: absolute;
        top: 9px;
        right: 20px;
    }
    .inbox-massage-content-main .card-body {
        padding: 20px;
    }

    @media screen and (min-width: 992px) {
        .mw-lg-300px {
            max-width: 300px;
        }
    }
    .ic-18 {
        width: 18px;
        height: 18px;
    }

    .img-tick {
        position: absolute;
        bottom: 4px;
    }
    .form-search,
    .form-search:hover,
    .form-search:active,
    .form-search:focus {
        background-image: url(data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMjQiIGhlaWdodD0iMjQiIHZpZXdCb3g9IjAgMCAyNCAyNCIgZmlsbD0ibm9uZSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHBhdGggZD0iTTE5LjM1IDIwLjQyNUwxMy4zMjUgMTQuNEMxMi44MjUgMTQuODMzMyAxMi4yNDE3IDE1LjE3MDggMTEuNTc1IDE1LjQxMjVDMTAuOTA4MyAxNS42NTQyIDEwLjIgMTUuNzc1IDkuNDUgMTUuNzc1QzcuNjUgMTUuNzc1IDYuMTI1IDE1LjE1IDQuODc1IDEzLjlDMy42MjUgMTIuNjUgMyAxMS4xNDE3IDMgOS4zNzUwMUMzIDcuNjA4MzQgMy42MjUgNi4xMDAwMSA0Ljg3NSA0Ljg1MDAxQzYuMTI1IDMuNjAwMDEgNy42NDE2NyAyLjk3NTAxIDkuNDI1IDIuOTc1MDFDMTEuMTkxNyAyLjk3NTAxIDEyLjY5NTggMy42MDAwMSAxMy45Mzc1IDQuODUwMDFDMTUuMTc5MiA2LjEwMDAxIDE1LjggNy42MDgzNCAxNS44IDkuMzc1MDFDMTUuOCAxMC4wOTE3IDE1LjY4MzMgMTAuNzgzMyAxNS40NSAxMS40NUMxNS4yMTY3IDEyLjExNjcgMTQuODY2NyAxMi43NDE3IDE0LjQgMTMuMzI1TDIwLjQ3NSAxOS4zNUMyMC42MjUgMTkuNDgzMyAyMC43IDE5LjY1NDIgMjAuNyAxOS44NjI1QzIwLjcgMjAuMDcwOCAyMC42MTY3IDIwLjI1ODMgMjAuNDUgMjAuNDI1QzIwLjMgMjAuNTc1IDIwLjExNjcgMjAuNjUgMTkuOSAyMC42NUMxOS42ODMzIDIwLjY1IDE5LjUgMjAuNTc1IDE5LjM1IDIwLjQyNVpNOS40MjUgMTQuMjc1QzEwLjc3NSAxNC4yNzUgMTEuOTI1IDEzLjc5NTggMTIuODc1IDEyLjgzNzVDMTMuODI1IDExLjg3OTIgMTQuMyAxMC43MjUgMTQuMyA5LjM3NTAxQzE0LjMgOC4wMjUwMSAxMy44MjUgNi44NzA4NCAxMi44NzUgNS45MTI1MUMxMS45MjUgNC45NTQxNyAxMC43NzUgNC40NzUwMSA5LjQyNSA0LjQ3NTAxQzguMDU4MzMgNC40NzUwMSA2Ljg5NTgzIDQuOTU0MTcgNS45Mzc1IDUuOTEyNTFDNC45NzkxNyA2Ljg3MDg0IDQuNSA4LjAyNTAxIDQuNSA5LjM3NTAxQzQuNSAxMC43MjUgNC45NzkxNyAxMS44NzkyIDUuOTM3NSAxMi44Mzc1QzYuODk1ODMgMTMuNzk1OCA4LjA1ODMzIDE0LjI3NSA5LjQyNSAxNC4yNzVaIiBmaWxsPSJibGFjayIvPgo8L3N2Zz4K);
        background-repeat: no-repeat;
        background-position: left 7px center;
        background-size: 24px 24px;
        padding-left: 40px;
        height: 36px;
        width: 200px;
        border: 1px solid #DDDDDD;
        border-radius: 0px;
    }
    textarea.form-control {     line-height: normal; }
    .Search-close-icon {
        position: absolute;
        top: 5px;
        right: 7px;
    }
    .ui-menu-item .ui-menu-item-wrapper:hover{
        background-color: #5F60B9;
        border-color: #5F60B9;

    }

</style>
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-12">
            <div class="card card-block card-stretch">
                <div class="card-body p-0">
                    <div class="d-flex justify-content-between align-items-center p-3 flex-wrap gap-3">
                        <h5 class="font-weight-bold">Chat</h5>
                    </div>

                </div>
            </div>
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <div class="row mt-30 ms-2px">
                            <div class="col-lg-6 col-md-6 col-sm-12 ps-0 inboxwidth">
                                <div class="inbox-massage-sec bg-white">
                                <div class="inbox-massage p-20 d-flex justify-content-between align-items-center">
                                    <h2 class="font-15 fw-500">Messages</h2>

                                    <div class="responsive-smbottom position-relative">
                                       <input type="text" placeholder="Search" name="search" id="search-user" class="form-control form-search">
                                       <div class="Search-close-icon">
                                            <img src="{{asset('img/ic_cancel.svg') }}">
                                        </div>
                                    </div>
                                </div>
                                <div class="inbox-massage-content-main">
                                    <div class="loader-div">
                                        <img src="{{asset('images/loader.gif')}}"/>
                                    </div>
                                </div>
                                </div>
                            </div>
                            <div class="col-lg-6 col-md-6 col-sm-12 inboxwidthtwo">
                                <div class="Quick-Chat-sec-main">
                                <div class="Quick-Chat-sec-head bg-white p-20">
                                    <h2 class="font-15 fw-500" id="chat_user_name"></h2>
                                </div>
                                <div class="Quick-middle-sec position-relative">
                                    <div id="chat-messages" class="Quick-middle-chat-main">

                                    </div>
                                </div>
                                <div class="Quick-footer position-relative">
                                    <textarea id="chat-input" class="form-control form-control-flush" rows="1" placeholder="Type a message"></textarea>
                                    <input type="hidden" name="" id="user_chat_id">
                                    <input type="hidden" name="" id="auth_user_name">
                                    <input type="hidden" name="" id="chat_uid">
                                    <input type="hidden" name="" id="user_chat_attach">
                                    <div class="Quick-footer-attachfile">
                                        <button type="button" id="attachButton" class="btn btn-icon me-10" data-bs-toggle="tooltip" data-bs-placement="top" title="">
                                            <img src="{{asset('img/ic_attach_file.svg') }}">
                                        </button>
                                        <input type="file" id="fileInput" style="display: none;">
                                        <button id="send-message" type="button" class="btn btn-icon" data-bs-toggle="tooltip" data-bs-placement="top" title="" disabled>
                                            <img src="{{asset('img/ic_send.svg') }}">
                                        </button>
                                    </div>
                                </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>


</x-master-layout>
<script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-app.js"></script>
<!-- Firebase Firestore -->
<script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-firestore.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-storage.js"></script>
<script src="https://www.gstatic.com/firebasejs/8.6.8/firebase-database.js"></script>


<script>
    const authChatId = '6pbp1gtWq2cGCtF6h8eXdkQon6z2';// $('#auth_user_id').val(); //'292z5msg0LNCCMC7mQ35cZuPwji1'; //'BVQSOyd8X9QXx366BRbED1gJwWL2';

    const firebaseConfig = {
        apiKey: "AIzaSyDDTWvEUlDvId8UhjW4pN5Y0D3ZCOqIamA",
        authDomain: "civilbook.firebaseapp.com",
        projectId: "civilbook",
        storageBucket: "civilbook.appspot.com",
        messagingSenderId: "1050520932388",
        appId: "1:1050520932388:web:ed08672bd44a99176248b1",
        measurementId: "G-229ZNH9M8V",
    };

    firebase.initializeApp(firebaseConfig);

    const db = firebase.firestore();
    const storage = firebase.storage();
    function uploadFile(file) {
        $('#send-message').attr('disabled');
        // Create a storage reference
        const storageRef = storage.ref();
        const fileRef = storageRef.child('chat_files/' + file.name);

        // Upload the file
        const uploadTask = fileRef.put(file);

        // Monitor the upload progress
        uploadTask.on('state_changed', function(snapshot) {
            var progress = (snapshot.bytesTransferred / snapshot.totalBytes) * 100;
            console.log('Upload is ' + progress + '% done');
        }, function(error) {
            console.error('Upload failed:', error);
        }, function() {
            uploadTask.snapshot.ref.getDownloadURL().then(function(downloadURL) {
                $('#send-message').removeAttr('disabled');
                console.log('File available at', downloadURL);
                $('#user_chat_attach').val(downloadURL);
                // Send the message with the attachment URL
                sendMessageNew();
                // sendMessage($('#chat-input').val(), downloadURL);
            });
        });
    }
    async function sendMessageNew(){
        var timestamp = firebase.firestore.FieldValue.serverTimestamp();

        // event.preventDefault(); // Prevent the form from submitting the traditional way
        var message = $('#chat-input').val();
        var attachmentURL = [];
        if($('#user_chat_attach').val() != ''){
            attachmentURL.push($('#user_chat_attach').val());
        }

        // Example user data
        var userName = $('#auth_user_name').val(); // Replace with actual user name
        var user_chat_id = $('#user_chat_id').val();
        const userId = $(`#${user_chat_id}`).data("id")

        var createdAt = Date.now();
        // Save the message to Firestore
        messageData = {
            createdAt: createdAt,
            message: message,
            senderId: authChatId,
            isMessageRead: false,
            receiverId: user_chat_id,
            photoUrl: '',
            attachmentfiles: attachmentURL,
            createdAtTime: timestamp,
            updatedAtTime: timestamp,
            messageType: (attachmentURL != '') ? 'FILES' : 'TEXT',
        };

        await addMessage(messageData);

        $.ajax({
            type: "POST",
            url: "{{url('api/send-chat-notification')}}",
            data: {
                receiver_id: userId
            }, // serializes the form's elements.
            success: function (e) {
                console.log("e================", e);
            },
            error: function (error) {
                console.log("error================", error);
            },
            cache: false,
        });

        $('#chat-input').val('');
        $('#user_chat_attach').val('');
        $('#send-message').attr('disabled');
    }
    function matchesPattern(string, pattern) {
       // Convert both the string and the pattern to lowercase for case-insensitive comparison
        var lowerCaseString = string.toLowerCase();
        var lowerCasePattern = pattern.toLowerCase();

        // Convert SQL-like pattern to a regular expression
        var regexPattern = lowerCasePattern
            .replace(/%/g, '.*') // Replace % with .*
            .replace(/_/g, '.')  // Replace _ with .
            .replace(/[-[\]/{}()*+?.\\^$|]/g, '\\$&'); // Escape special characters
        var regex = new RegExp(regexPattern); // Create a regex without case sensitivity

        return regex.test(lowerCaseString);
    }

    $(document).ready(function() {
        $('.loader-div').hide();
        var user_chat_list = [];
        $('#chat-input').change(function() {
            if($(this).val() === ''){
                $('#send-message').attr('disabled');
            }
            else{
                $('#send-message').removeAttr('disabled');
            }

        });
        $('#attachButton').click(function() {
            $('#fileInput').click();
        });
        $('#fileInput').change(function() {
            // Handle the file selection
            const file = this.files[0];
            if (file) {
                uploadFile(file);
                console.log('File selected:', file.name);
            }
        });
        const chatList = $('.inbox-massage-content-main');
        const chatDetail = $('#chat-messages');

        function resetinput(){
            $('#chat-input').val('');
            $('#user_chat_attach').val('');
            $('#user_chat_id').val('');
        }
        $("#search-user").autocomplete({
                source: user_chat_list,
                focus: function(event, ui) {
                    // Prevent the value from being updated on focus
                    return false;
                },
                select: function(event, ui) {
                    // Set the input field value to the selected item's label
                    $("#search-user").val(ui.item.label);
                    // Do something with the selected item's value if needed
                    if(ui.item.value){
                        resetinput();
                        chatSnap(ui.item.value, 'offload');
                    }
                    console.log("Selected ID: " + ui.item.value);
                    return false;
                }
        }).autocomplete("instance")._renderItem = function(ul, item) {
                return $("<li>")
                    .append("<div>" + item.label + "</div>")
                    .appendTo(ul);
        };
        $(document).on('click', '.Search-close-icon', function () {
            $("#search-user").val(''); // Clear the input field
            $("#search-user").autocomplete("close"); // Close the autocomplete suggestions
            resetinput();
            chatSnap('', 'offload');
            $('.inbox-massage-content-main').html('');
        });

        function getUnreadCount(userId) {
            return new Promise((resolve, reject) => {
                db.collection('messages')
                    .doc(userId)
                    .collection(authChatId)
                    .where('isMessageRead', '==', false)
                    .where('receiverId', '==', authChatId)
                    .onSnapshot(snapshot => {
                        const unreadCount = snapshot.docs.length;
                        resolve(unreadCount <= 99 ? unreadCount : "99+");
                    }, error => {
                        console.error(error);
                        reject(0);
                    });
            });
        }


        async function chatSnap(search = '', load = '') {
            $('.loader-div').show();
            // console.log('search:'+ search);
            db.collection("users").doc(authChatId).onSnapshot(function (snapshot) {
            chatList.empty(); // Clear the current chat list
            chatDetail.empty();
            $('#chat_user_name').html('')
            //.collection('contact')

            if (snapshot.exists) {
                // Access fields of the user document
                var userData = snapshot.data();

                $('#auth_user_name').val(userData.display_name);

                var subCollectionRef = snapshot.ref.collection('contact').orderBy('lastMessageTime', 'desc');
                subCollectionRef.onSnapshot((subCollectionSnapshot) => {
                    $('.inbox-massage-content-main').html('');
                    chatList.empty();
                    subCollectionSnapshot.forEach(async (subDoc) => {

                        let userName;
                        let profile_image;
                        // Access fields of documents in sub-collection
                        const subData = subDoc.data();

                        if(search != '' & subData.uid == search){ console.log('if');
                            chatList.empty();

                            db.collection('users').doc(subData.uid).get().then((user_snapshot) => {

                                var currentActiveChat = $('#user_chat_id').val();
                                var user_type = typeof user_snapshot.data().user_type != 'undefined'  ? ' - <span style=" text-transform: capitalize; ">'+user_snapshot.data().user_type+'</span>' : '';
                                profile_image = (typeof user_snapshot.data().profile_image === "undefined") ? '' : user_snapshot.data().profile_image;
                                if (user_snapshot.exists) {
                                    userName = user_snapshot.data().display_name;
                                    // Display the message along with the user's name

                                } else {
                                    userName = user_snapshot.data().name;
                                }

                                var dateTime = new Date(subData.lastMessageTime);
                                var dateTimeFormat = dateTime.toLocaleString();

                                // const chatItem = $('<div id= ' + subData.uid + ' class="user-chat">').text(subData.uid); // Adjust according to your data structure

                                const chatItem = $('<div class="card-body user-chat pb-0" id= ' + subData.uid + ' data-name="' + userName + '" data-id="' + user_snapshot.data().id + '"> <div class="row"> <div class="col-auto pe-20"> <img style="width:50px !important; height: 50px !important;" src="' + profile_image + '" srcset="' + profile_image + '" class="img-fluid"> </div> <div class="col border-bottom-inbox ps-0 pb-20"> <div class="d-flex justify-content-between mb-1"> <p class="font-13 fw-500">' + userName + '' + user_type + '</p> <p class="font-13 fw-500">' + dateTimeFormat + '</p> </div> <p class="massage-pag font-14 fw-400 grey-clr"> </p> </div> </div> </div>'); // Adjust according to your data structure
                                chatList.append(chatItem);

                            });
                        }
                        else{
                            if(search == ''){
                                db.collection('users').doc(subData.uid).get().then(async (user_snapshot) => {

                                    var user_type = typeof user_snapshot.data().user_type != 'undefined' ? ' - <span style=" text-transform: capitalize; ">' + user_snapshot.data().user_type + '</span>' : '';
                                    profile_image = (typeof user_snapshot.data().profile_image === "undefined") ? '' : user_snapshot.data().profile_image;
                                    if (user_snapshot.exists) {
                                        userName = user_snapshot.data().display_name;
                                        // Display the message along with the user's name

                                    } else {
                                        userName = user_snapshot.data().name;
                                    }
                                    if (load == 'load') {

                                        var result = $.grep(user_chat_list, function (item) {
                                            return item.value === subData.uid;
                                        });

                                        if (result.length == 0) {
                                            user_chat_list.push({value: subData.uid, label: userName});
                                        }
                                    }

                                    var dateTime = new Date(subData.lastMessageTime);
                                    var dateTimeFormat = dateTime.toLocaleString();
                                    const count = await getUnreadCount(subData.uid)

                                    // const chatItem = $('<div id= ' + subData.uid + ' class="user-chat">').text(subData.uid); // Adjust according to your data structure

                                    let chatClass = "unread-message";
                                    let displayCount = ''

                                    if (count === 0) {
                                        chatClass = ""
                                        displayCount = 'display-none'
                                    }

                                    const chatItem = $('<div class="card-body ' + chatClass + ' user-chat pb-0" id= ' + subData.uid + ' data-name="' + userName + '" data-id="' + user_snapshot.data().id + '"> <div class="row"> <div class="col-auto pe-20"> <img style="width:50px !important; height: 50px !important;" src="' + profile_image + '" srcset="' + profile_image + '" class="img-fluid"> </div> <div class="col border-bottom-inbox ps-0 pb-20"> <div class="d-flex justify-content-between mb-1"> <p class="font-13 fw-500">' + userName + '' + user_type + '</p> <div style="display: flex;flex-direction: column;align-items: end"><p id="count_' + subData.uid + '" class="unread-count-badge ' + displayCount + ' ">' + count + '</p><p class="font-13 fw-500">' + dateTimeFormat + '</p></div></div> <p class="massage-pag font-14 fw-400 grey-clr"> </p> </div> </div> </div>'); // Adjust according to your data structure
                                    chatList.append(chatItem);
                                });
                            }
                        }
                        $('#'+subData.uid).addClass('active');
                        $('.loader-div').hide();
                    });
                }, (error) => {
                    console.log("Error getting sub-collection documents: ", error);
                });
            } else {
                console.log("User document does not exist.");
            }
        });
        }

        function markAsUnRead(userId) {
            db.collection('messages')
                .doc(userId)
                .collection(authChatId)
                .where('isMessageRead', '==', false)
                .where('receiverId', '==', authChatId)
                .get()
                .then(snapshot => {
                    const batch = db.batch();

                    snapshot.docs.forEach(doc => {
                        const messageRef = doc.ref;
                        batch.update(messageRef, {isMessageRead: true});
                    });

                    return batch.commit();
                })
                .then(() => {
                    console.log('All messages marked as read.');
                })
                .catch(error => {
                    console.error('Error updating messages:', error);
                });
        }

        chatSnap('', 'load');

        $(document).on('click', '.user-chat', function () {
            resetinput();
            $('.user-chat').removeClass('active');
            $(this).addClass('active');
            const uId = this.id;
            markAsUnRead(uId)

            const countP = $('#count_' + uId);
            countP.removeClass('unread-count-badge')
            countP.addClass('display-none')

            $("#" + uId).removeClass('unread-message')

            $('#user_chat_id').val(uId);
            var username = $(this).attr('data-name');

            $('#chat_user_name').html('Chat with '+username);

            db.collection("messages").doc(authChatId).collection(uId).orderBy('createdAt', 'asc').onSnapshot(function (snapshot) {

                // const messageRef = db.collection('messages').doc('MESSAGE_ID');
                // console.log(messageRef);
                // // Get the current user ID (this might come from authentication)
                // const userId = 'USER_ID';

                // // Update the document to add the user ID to the 'readBy' array
                // messageRef.update({
                // readBy: firebase.firestore.FieldValue.arrayUnion(userId)
                // })

                chatDetail.empty();
                snapshot.forEach(function (doc) {
                    const chat = doc.data();
                    var isRead = chat.isMessageRead;
                    let chatMessage = "";
                    var chatMessageImg = isReadSpan = '';
                    if (chat.senderId == authChatId) {
                        isReadSpan = isRead == true ? '<img src="{{asset("img/ic-double-check.svg") }}" class="ic-18 img-tick">' : '<img src="{{asset("img/ic-check.svg") }}" class="ic-18 img-tick">';
                        var createdAtTime = new Date(chat.createdAtTime * 1000);
                        var createdAtTimeFormat = createdAtTime.toLocaleString("sv-SE", { timeStyle:'short' });
                        if(chat.attachmentfiles.length > 0){
                            chatMessageImg = '<a target="new" href="'+chat.attachmentfiles+'"> <img width="60" height="60" src='+chat.attachmentfiles+'></a>';
                        }
                        chatMessage = '<div class="send-massage d-flex justify-content-end d-flex mb-3 position-relative"> <div class="custom-space mw-lg-300px font-14 fw-400 bg-darkblue text-light position-relative"> '+chat.message+' <span  class="time-sec font-10 fw-400 text-light ms-3">'+createdAtTimeFormat+'</span> '+chatMessageImg+' '+isReadSpan+' </div> </div>';
                        // chatMessage = $('<div style="margin-bottom: 5px" class="send-massage-place send-massage">').text(chat.message);
                    } else {

                        var createdAtTime = new Date(chat.createdAtTime * 1000);
                        var createdAtTimeFormat = createdAtTime.toLocaleString("sv-SE", { timeStyle:'short' });
                        if(chat.attachmentfiles.length > 0){
                            chatMessageImg = '<a target="new" href="'+chat.attachmentfiles+'"> <img width="60" height="60" src='+chat.attachmentfiles+'></a>';
                        }
                        chatMessage = '<div class="receive-massage d-flex justify-content-start d-flex mb-3 position-relative"> <div class="custom-space mw-lg-300px font-14 fw-400 bg-white position-relative"> '+chat.message+' <span  class="time-sec font-10 fw-400 grey-clr ms-3">'+createdAtTimeFormat+'</span> '+chatMessageImg+'</div> </div>';
                        // chatMessage = $('<div style="margin-bottom: 5px" class="receive-massage-place receive-massage">').text(chat.message);
                    }

                    chatDetail.append(chatMessage);
                });
            });
        });

        $('#send-message').click(function() {
            sendMessageNew();

        });


        function sendMessage(conversationId, senderId, text) {
            fetch('/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    conversation_id: conversationId,
                    sender_id: senderId,
                    text: text,
                }),
            })
            .then(response => response.json())
            .then(data => {
                console.log('Message sent:', data);
            })
            .catch((error) => {
                console.error('Error sending message:', error);
            });
        }
    });

    // Function to add message
    async function addMessage(data, receiverBlockedMe = false) {
        try {
            // Sender collection and document
            var senderCollection = db.collection('messages').doc(data.senderId).collection(data.receiverId);
            var senderDoc = await senderCollection.add(data);
            await senderDoc.update({ uid: senderDoc.id });

            if (!receiverBlockedMe) {
                // Receiver collection and document
                var receiverCollection = db.collection('messages').doc(data.receiverId).collection(data.senderId);
                var receiverDoc = await receiverCollection.add(data);
                await receiverDoc.update({ uid: receiverDoc.id });
            }
            return senderDoc;
        } catch (error) {
            console.error('Error adding message: ', error);
        }
    }
</script>
