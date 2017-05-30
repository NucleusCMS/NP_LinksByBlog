<?php

/*
  LinksByBlog maintains and displays independent collections of links for
  each weblog in a Nucleus installation. To display the list include <%LinksByBlog%> in the
  skin where you want the list to appear. If the current user is logged in as admin then
  list management functions will also be displayed.
*/

class NP_LinksByBlog extends NucleusPlugin {
    function getName ()     {return 'LinksByBlog'; }
    function getAuthor ()   {return 'Jim Stone + Fel'; }
    function getURL ()      {return 'www.justblogit.com';}
    function getVersion ()  {return '0.3';}
    function getTableList() {return array('nucleus_plug_linksbyblog');}
    function getMinNucleusVersion() {return '350';}
    function getDescription () {
        return 'LinksByBlog maintains and displays separate collections of links for each weblog in a Nucleus site. To display the links use &lt;%LinksByBlog%&gt; in the skin. Admin features are available for users logged in as admin.';}
    function supportsFeature($w) {return in_array($w,array('SqlApi','SqlTablePrefix'));}
    
    function doSkinVar($skinType, $what = 'list') {
        global $CONF, $member, $blog;
        $actionURL = $CONF['ActionURL'];
        if(!$blogid) return;
        $blogid = $blog->getID();
        $query = "SELECT id,title,url,description FROM nucleus_plug_linksbyblog WHERE blogid = $blogid ORDER BY title";
        $links = sql_query ($query);
        $AdminLogon = $member->isLoggedIn() && $member->blogAdminRights($blog->getID());
        if (sql_num_rows($links) > 0){
            echo '<ul class="nobullets">';
            while ($link = sql_fetch_object($links)){
                echo sprintf('<li><a href = "%s" title = "%s" target = "blank">%s</a>',hsc($link->url),hsc($link->description),hsc($link->title));
                if ($AdminLogon) {
                    echo sprintf('&nbsp;<a href="%s?action=plugin&amp;name=LinksByBlog&amp;type=delete&amp;blogid=%s&amp;linkid=%s->id">[DEL]</a>',$actionURL,$blogid,$link);
                }
                echo "</li>";
            }
            echo "</ul>";
        }
        if ($AdminLogon){
          echo '<div style = "border-top: 1px dotted black;margin-top: 10px"><b>Add A Link</b>';
          $this->showAddForm($blogid);
          echo '</div>';
        }
        return;
    }
    
    function doAction($type) {
        switch($type) {
            case 'delete':
                $link = requestVar('linkid');
                $blog = requestVar('blogid');
                sql_query ("DELETE FROM nucleus_plug_linksbyblog WHERE (blogid='$blog') AND (id = '$link')");
                header('Location: ' . $_SERVER ['HTTP_REFERER']);
                break;
          case 'AddLink':
                $site = requestVar('address');
                $site = sql_real_escape_string($site);
                $title = requestVar('title');
                $title = sql_real_escape_string($title);
                $description = requestVar('desc');
                $description = sql_real_escape_string($description);
                $url = requestVar('url');
                $url = sql_real_escape_string($url);
                $HasHTTP = strpos($url,'http://').'' ; 
                
                if ($HasHTTP === false) $url = "http://".$url;
                
                $blogid = requestVar ('blogid');
                $insert = "insert into nucleus_plug_linksbyblog (blogid,title,url,description) values ('$blogid','$title','$url','$description')";
                sql_query ($insert);
                header('Location: ' . $_SERVER ['HTTP_REFERER']);
                break;
          default:
        }
    }
    
    function showAddForm ($blogid) {
    global $CONF;
    ?>
    <form method="post" action="<?php echo $CONF['ActionURL'] ?>">
            <div><input type="hidden" name="action" value="plugin"/>
            <input type="hidden" name="name" value="LinksByBlog" />
            <input type="hidden" name="type" value="AddLink" />
            <input type="hidden" name="blogid" value = "<?php echo "$blogid"; ?>" />
            <input type="text" name="title" id="sitetitle" value="Site title" size="20" style = "font-size: x-small;" /><br />
            <input type="text" name="desc" id="desc" value = "Description" size = "20" style = "font-size: x-small;" /><br />
            <input type="text" name="url" id="url" value = "http://" size = "20" style = "font-size: x-small;" /><br />
            <input type="submit" value="Add the Link" style = "font-size: x-small;" /></div>
        </form>
        <?php
    }
    
    function install() {
    sql_query ("CREATE TABLE nucleus_plug_linksbyblog (
                id int(11) not null auto_increment,
                blogid int(11) not null,
                title varchar(128) not null default '',
                url varchar(255) not null default '',
                description varchar(512) not null default '',
                primary key (id))");
    }
    
    function unInstall() {sql_query ('DROP TABLE nucleus_plug_linksbyblog');}
}
