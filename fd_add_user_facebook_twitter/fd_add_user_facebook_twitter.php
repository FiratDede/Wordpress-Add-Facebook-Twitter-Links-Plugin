<?php
/*
 * Plugin name: Add User's Facebook or Twitter Link Plugin 
 * Description: Adds facebook and twitter links to user profiles.If a user wants, the user can show them in your posts. Also admin can change the links of users.
 * Author: Fırat Dede
 *License:     GPL2

*/

add_action( 'show_user_profile', "fd_show_user",10,1);
function fd_show_user($user){
    ?>
<script>
    jQuery(document).ready(function($){
        $("  <tr > <th><label >Facebook Link</label> </th> <td><input type='url' class='regular-text code' value='<?php  echo get_user_meta($user->ID, "fd_facebook_link",true); ?>' "+"name='fd_facebook_link'><br> </td> </tr> <tr id= 'fd_twitter_link' >  <th> <label >Twitter Link</label> </th>  <td> <input type='url' class='regular-text code' name='fd_twitter_link'  value='<?php echo get_user_meta($user->ID, "fd_twitter_link",true);  ?> ' "+"> <br> </td> </tr> ").insertAfter(".user-description-wrap"); 
        $("<tr> <th> <label>Show my links on my posts  </label>  </th>  <td> <input type='checkbox' name='fd_show_my_links' value='checked' <?php if(get_user_meta($user->ID, "fd_check_showing_my_links",true)=="checked") echo "checked";  ?> >  </td>  </tr>").insertAfter("#fd_twitter_link");
    
    })
 </script>
    <?php   
}
add_action( "edit_user_profile", "fd_edit_user_profile", 10,1 );
function fd_edit_user_profile($profileuser ){
    ?>
<script>
    
    jQuery(document).ready(function($){
        $("  <tr > <th><label >Facebook Link</label> </th> <td><input type='url' class='regular-text code' value='<?php  echo get_user_meta($profileuser->ID, "fd_facebook_link",true); ?>' "+"name='fd_facebook_link'><br> </td> </tr> <tr >  <th> <label >Twitter Link</label> </th>  <td> <input type='url' class='regular-text code' name='fd_twitter_link' value='<?php echo get_user_meta($profileuser->ID, "fd_twitter_link",true);  ?> ' "+"> <br> </td> </tr> ").insertAfter(".user-description-wrap"); 
    })

 </script>

    <?php

}
add_action("wp_head","fd_add_user_facebook_twitter_required_libraries");
function fd_add_user_facebook_twitter_required_libraries(){
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.14.0/css/all.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js">    </script>
<?php
}
add_action("wp_head","fd_add_scripts_facebook_twitter_links");

function fd_add_scripts_facebook_twitter_links(){
    ?>
    <script > 
    jQuery(document).ready(function($){
        $(".fd_author_facebook_link, .fd_author_twitter_link").css("color","black");
        $(".fd_author_facebook_link").mouseover(function(){
           this.style.color="#3b5998";
        })
        $(".fd_author_twitter_link").mouseover(function(){
            this.style.color="#1DA1F2";
        })
        $(".fd_author_facebook_link, .fd_author_twitter_link").mouseleave(function(){
       this.style.color="black";
         
     })

    })
    

    </script>

    <?php

}
add_action("the_content","fd_add_facebook_twitter_icons",10,1);
function fd_add_facebook_twitter_icons($content){
    global $post;
  if(!is_page(  )&&get_user_meta( $post->post_author, "fd_check_showing_my_links", true)=="checked"){
    $link1=get_user_meta( $post->post_author, "fd_facebook_link", true ); 
    $link2=get_user_meta( $post->post_author, "fd_twitter_link", true ); 
    $content.=" <div> <a class='fd_social_media_links'  target='_blank' rel='noopener noreferrer' href='".$link1."'> <i class='fab fa-facebook-square fd_author_facebook_link'></i></a>    <a  class='fd_social_media_links' target='_blank' rel='noopener noreferrer' href='".$link2."'><i class='fab fa-twitter fd_author_twitter_link'></i></a> </div>   ";
  }  
  return $content;
}
register_deactivation_hook( __FILE__, "fd_add_user_facebook_twitter_deactivation_hook");
function fd_add_user_facebook_twitter_deactivation_hook(){
    $all_users=get_users( ["fields"=>"ID","meta_key"=>"fd_facebook_link"] );
    foreach ($all_users as $user_id){
    delete_user_meta( $user_id, "fd_facebook_link");
    delete_user_meta( $user_id, "fd_twitter_link");
    delete_user_meta( $user_id, "fd_check_showing_my_links");
    delete_user_meta( $user_id, "fd_facebook_link_error");
    delete_user_meta( $user_id, "fd_twitter_link_error");

    }
}
add_action("user_profile_update_errors","fd_check_facebook_twitter_links",10,3);
function fd_check_facebook_twitter_links($errors,  $update,  $user){
    if($update==true){
       
        $safe_facebook_link= sanitize_url( $_POST["fd_facebook_link"]);
        $safe_twitter_link= sanitize_url( $_POST["fd_twitter_link"]);
            
        if(preg_match("/facebook.com/i",$safe_facebook_link)==1||$safe_facebook_link==""){
            update_user_meta( $user->ID, "fd_facebook_link", $safe_facebook_link);
            update_user_meta( $user->ID, "fd_facebook_link_error", 0);   
            
        }
        else {
            update_user_meta( $user->ID, "fd_facebook_link_error", 1);
        }
            
        if(preg_match("/twitter.com/i",$safe_twitter_link)==1||$safe_twitter_link==""){
         update_user_meta( $user->ID, "fd_twitter_link", $safe_twitter_link);
         update_user_meta( $user->ID, "fd_twitter_link_error", 0);
         }
        else{
            update_user_meta( $user->ID, "fd_twitter_link_error", 1);
        }
            
        if(isset($_POST["fd_show_my_links"])){
            update_user_meta( $user->ID, "fd_check_showing_my_links",$_POST["fd_show_my_links"] );
        }
        else{
            update_user_meta( $user->ID, "fd_check_showing_my_links","unchecked" );
        }
        if(get_user_meta( $user->ID, "fd_facebook_link_error", true )==1)
            $errors->add("__fd_facebook_link_error","<strong>Error</strong>: Enter a valid facebook link");
        else{
            $errors->remove("__fd_facebook_link_error");
        }
        if(get_user_meta( $user->ID, "fd_twitter_link_error", true )==1)
            $errors->add("__fd_twitter_link_error","<strong>Error</strong>: Enter a valid twitter link");
        else
            $errors->remove("__fd_twitter_link_error");

    }
}