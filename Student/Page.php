<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Chirag Social</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="stylesheet" href="./styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.15.4/css/all.css" integrity="sha384-DyZ88mC6Up2uqS4h/KRgHuoeGwBcD4Ng9SiP4dIRy0EXTlnuz47vAwmeGwVChigm" crossorigin="anonymous">
</head>
<body>
    <nav>
        <div class="container">
            <img src="images/12.png" alt="Buddy Logo" style="width: 50px; margin-left: 100px;">
            <div class="search-bar">
                <i class="uil uil-search"></i>
                <input type="search" placeholder="Search for creators, inspirations and projects">
            </div>
            <div class="create-group">
                <label class="btn btn-primary" for="create-group">Create Page</label>
            </div>
            <div class="profile-pic" id="my-profile-picture" style="height: 50px; width: 50px">
                <img src="images/1.jpg" alt="pic 1">
            </div>
        </div>
    </nav>
    <div class="profile-container" style="margin-top: 50px;">
        <div class="profile-info">
            <div class="info-col">
                <div class="profile-intro">
                    <h3>Create a Page</h3>
                    <hr>
                    <form class="create-group-form" action="path_to_your_php_script" method="post">
                        <label for="group-name">Page Name:</label>
                        <input type="text" id="group-name" name="group_name" required>

                        <label for="group-picture">Profile Picture:</label>
                        <input type="file" id="group-picture" name="group_picture" accept="image/*" required>
                        <button type="submit">Create Page</button>
                    </form>
                </div>
                <div class="profile-intro">
                    <div class="title-box">
                        <h3>Pages</h3>
                        <a href="#">All Pages</a>
                    </div>
                    <hr>
                    <div class="groups">
                        <div class="group-item">
                            <img src="images/68.png" alt="GEEKS">
                            <div class="name-verification">
                                <span>Buddy</span>
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="group-item">
                            <img src="images/spu.png" alt="Sports Team">
                            <div class="name-verification">
                                <span>sol plaatjie university</span>
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="group-item">
                            <img src="images/84.jpg" alt="Study Groups">
                            <div class="name-verification">
                                <span>SPU Gamers</span>
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="group-item">
                            <img src="images/85.jpg" alt="Event Planning Committee">
                            <div class="name-verification">
                                <span>Event Planning Committee</span>
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="group-item">
                            <img src="images/86.png" alt="Research and Innovation Club">
                            <div class="name-verification">
                                <span>Research and Innovation Club</span>
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="group-item">
                            <img src="images/87.png" alt="Cultural Exchange Group">
                            <div class="name-verification">
                                <span>Cultural Exchange</span>
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                    </div>                    
                </div>
            </div>
            <div class="post-col">
                <div class="feeds">
                    <div class="feed">
                        <div class="feed-top">
                            <div class="user">
                                <div class="profile-pic">
                                    <img src="images/68.png" alt="">
                                </div>
                                <div class="profile-pic" id="my-profile-picture">
                                    <img src="images/1.jpg" alt="pic 1">
                                    
                                </div>
                                <div class="info">
                                    <div class="name-verification">
                                        <h3>Buddy</h3>
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <div class="name-verification">
                                        <small class="bigger">Mosa Potsane</small>
                                        <i class="fas fa-check-circle"></i>
                                    </div>
                                    <small>Colesberg, 4 DAYS AGO</small>
                                </div>
                                <span class="edit">
                                    <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                    <ul class="edit-menu">
                                        <li><i class="fa fa-pen"></i>Edit</li>
                                        <li><i class="fa fa-trash"></i>Delete</li>
                                    </ul>
                                </span>
                            </div>
            
                                     <div class="feed-img">
                                         <img src="images/59.png" alt="">
                                     </div>
            
                                     <div class="action-button">
                                         <div class="interaction-button">
                                             <span><i class="fa fa-heart" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-share" aria-hidden="true"></i></span>
                                         </div>
                                         <div class="bookmark">
                                             <span><i class="fa fa-bookmark"></i></span>
                                         </div>
                                     </div>
            
                                     <div class="liked-by">
                                         <span><img src="images/profile-17.jpg"></span>
                                         <span><img src="images/profile-18.jpg"></span>
                                         <span><img src="images/profile-19.jpg"></span>
                                         <p>Liked by <b>Enrest Achiever</b>and <b>220 others</b></p>
                                     </div>
            
                                     <div class="caption">
                                        <div classs="title">
                                            could be anything
                                        </div>
                                         <p><b>Lana Rose</b>Lorem ipsum dolor storiesquiquam eius.
                                        <span class="hars-tag">#lifestyle</span></p>
                                     </div>
                                     <div class="comments text-gry">View all 130 comments</div>
                                    </div>
                                </div>
                            
                                <div class="feed">
                                    <div class="feed-top">
                                     <div class="user">
                                         <div class="profile-pic">
                                             <img src="images/spu.png" alt="">
                                         </div>
                                         <div class="profile-pic" id="my-profile-picture">
                                            <img src="images/1.jpg" alt="pic 1">
                                            
                                        </div>
                                        <div class="info">
                                            <div class="name-verification">
                                                <h3>sol plaatji university</h3>
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            
                                            <div class="name-verification">
                                                <small class="bigger">Mosa Potsane</small>
                                                <i class="fas fa-check-circle"></i>
                                            </div>
                                            <small>Kimberly, 16 DAYS AGO</small>
                                        </div>
                                         <SPAN class="edit">
                                            <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                            <ul class="edit-menu">
                                                <li><i class="fa fa-pen"></i></i>Edit</li>
                                                <li><i class="fa fa-trash"></i></i>Delete</li>
                                            </ul>
                                        </SPAN>
                                     </div>
            
                                     <div class="feed-img">
                                         <img src="images/88.jpg" alt="">
                                     </div>
            
                                     <div class="action-button">
                                         <div class="interaction-button">
                                             <span><i class="fa fa-heart" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-share" aria-hidden="true"></i></span>
                                         </div>
                                         <div class="bookmark">
                                             <span><i class="fa fa-bookmark"></i></span>
                                         </div>
                                     </div>
            
                                     <div class="liked-by">
                                        <span><img src="images/profile-12.jpg"></span>
                                        <span><img src="images/profile-14.jpg"></span>
                                        <span><img src="images/profile-16.jpg"></span>
                                         <p>Liked by <b>Enrest Achiever</b>and <b>220 others</b></p>
                                     </div>
            
                                     <div class="caption">
                                        <div classs="title">
                                            could be anything
                                        </div>
                                         <p><b>Lana Rose</b>Lorem ipsum dolor storiesquiquam eius.
                                        <span class="hars-tag">#lifestyle</span></p>
                                     </div>
                                     <div class="comments text-gry">View all 130 comments</div>
                                    </div>
                                </div>
                            
                                <div class="feed">
                                    <div class="feed-top">
                                        <div class="user">
                                            <div class="profile-pic">
                                                <img src="images/85.jpg" alt="">
                                            </div>
                                            <div class="profile-pic" id="my-profile-picture">
                                                <img src="images/1.jpg" alt="pic 1">
                                                
                                            </div>
                                            <div class="info">
                                                <div class="name-verification">
                                                    <h3>Event Planning Committee</h3>
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                
                                                <div class="name-verification">
                                                    <small class="bigger">Mosa Potsane</small>
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <small>Lost Angeles, 4 MONTHS AGO</small>
                                            </div>
                                            <div class="edit">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                <ul class="edit-menu">
                                                    <li><i class="fa fa-pen"></i></i>Edit</li>
                                                    <li><i class="fa fa-trash"></i></i>Delete</li>
                                                </ul>
                                            </div>
                                        </div>
            
                                     <div class="feed-img">
                                        <div classs="title">
                                            <h3>Want to host an event? Join us</h3>
                                        </div>
                                     </div>
            
                                     <div class="action-button">
                                         <div class="interaction-button">
                                             <span><i class="fa fa-heart" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-share" aria-hidden="true"></i></span>
                                         </div>
                                         <div class="bookmark">
                                             <span><i class="fa fa-bookmark"></i></span>
                                         </div>
                                     </div>
            
                                     <div class="liked-by">
                                         <span><img src="images/profile-13.jpg"></span>
                                         <span><img src="images/profile-11.jpg"></span>
                                         <span><img src="images/profile-10.jpg"></span>
                                         <p>Liked by <b>Enrest Achiever</b>and <b>220 others</b></p>
                                     </div>
            
                                     <div class="caption">
                                        <div classs="title">
                                            could be anything
                                        </div>
                                         <p><b>Lana Rose</b>Lorem ipsum dolor storiesquiquam eius.
                                        <span class="hars-tag">#lifestyle</span></p>
                                     </div>
                                     <div class="comments text-gry">View all 130 comments</div>
                                    </div>
                                </div>
                                
                                <div class="feed">
                                    <div class="feed-top">
                                        <div class="user">
                                            <div class="profile-pic">
                                                <img src="images/87.png" alt="">
                                            </div>
                                            <div class="profile-pic" id="my-profile-picture">
                                                <img src="images/1.jpg" alt="pic 1">
                                                
                                            </div>
                                            <div class="info">
                                                <div class="name-verification">
                                                    <h3>Cultural Exchange</h3>
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                
                                                <div class="name-verification">
                                                    <small class="bigger">Mosa Potsane</small>
                                                    <i class="fas fa-check-circle"></i>
                                                </div>
                                                <small>Paris, 8 HOURS AGO</small>
                                            </div>
                                            <SPAN class="edit">
                                                <i class="fa fa-ellipsis-v" aria-hidden="true"></i>
                                                <ul class="edit-menu">
                                                    <li><i class="fa fa-pen"></i></i>Edit</li>
                                                    <li><i class="fa fa-trash"></i></i>Delete</li>
                                                </ul>
                                            </SPAN>
                                        </div>
            
                                     <div class="feed-img">
                                         <img src="images/89.jpg" alt="">
                                     </div>
            
                                     <div class="action-button">
                                         <div class="interaction-button">
                                             <span><i class="fa fa-heart" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-comment-dots" aria-hidden="true"></i></span>
                                             <span><i class="fa fa-share" aria-hidden="true"></i></span>
                                         </div>
                                         <div class="bookmark">
                                             <span><i class="fa fa-bookmark"></i></span>
                                         </div>
                                     </div>
            
                                     <div class="liked-by">
                                        <span><img src="images/profile-11.jpg"></span>
                                        <span><img src="images/profile-19.jpg"></span>
                                        <span><img src="images/profile-13.jpg"></span>
                                         <p>Liked by <b>Enrest Achiever</b>and <b>220 others</b></p>
                                     </div>
            
                                     <div class="caption">
                                        <div classs="title">
                                            could be anything
                                        </div>
                                         <p><b>Lana Rose</b>Lorem ipsum dolor storiesquiquam eius.
                                        <span class="hars-tag">#lifestyle</span></p>
                                     </div>
                                     <div class="comments text-gry">View all 130 comments</div>
                                    </div>
                                </div>
            </div>
            </div>
        </div>
    </div>
</body>
</html>