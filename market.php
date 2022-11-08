<?php
/* The Market module by Tifa Zabat
An optional module for PHP versions of Legend of the Green Dragon.
This module adds a location called "The Market".
Allows users to have a space for OoC chatter without disturbing general roleplay.
Displays the latest MoTD, polls, and news, above the Market's chat window.
CC-BY-SA 4.0 github:tifasnow https://github.com/tifasnow/logd_market/
    */
    function market_getmoduleinfo(){
        $info = array(
            "name"=>"Market",
            "author"=>"Tifa Zabat",
            "version"=>"2022.11.08",
            "category"=>"Village",
            "download"=>"https://github.com/tifasnow/logd_market/",
            "prefs"=>array(
                "Market,title",
                "market"=>"Has the user visited the Market?,bool|0",
                "show_area"=>"Show the Market area?,bool|0",
            ),
            "settings"=>array(
                "Market Settings,title",
                "show_motd"=>"Show the latest MoTD?,bool|1",
                "show_poll"=>"Show the latest poll?,bool|1",
                "show_news"=>"Show the latest news?,bool|1",
                "show_chat"=>"Show the chat window?,bool|1",
                "show_nav"=>"Show the Market in the Navigation Menu?,bool|1",
                "nav_name"=>"What should the Market be called in the Navigation Menu?,text|Market",
            ),
        );
        return $info;
    }
    function market_install(){
        module_addhook("village");
        return true;
    }
    function market_uninstall(){
        return true;
    }
    function market_dohook($hookname,$args){
        global $session;
        switch($hookname){
            case "village":
                if (get_module_pref("show_area") == 1){
                    tlschema($args['schemas']['marketnav']);
                    addnav($args['marketnav']);
                    addnav(get_module_setting("nav_name"),"runmodule.php?module=market&op=enter");
                }
                break;
        }
        return $args;
    }
    function market_run()
    {
        global $session;
        $op = httpget('op');
        $show_motd = get_module_setting("show_motd");
        $show_poll = get_module_setting("show_poll");
        $show_news = get_module_setting("show_news");
        $show_chat = get_module_setting("show_chat");
        $nav_name = get_module_setting("nav_name");
        $market = get_module_pref("market");
        $show_area = get_module_pref("show_area");
        page_header($nav_name);
        $session['user']['specialinc'] = "module:market";
        if ($op == "enter") {
            output("`c`b`&$nav_name`0`b`c");
            if ($show_motd == 1) {
                output("`c`b`&The latest Message of the Day:`0`b`c");
                motd();
            }
            if ($show_poll == 1) {
                output("`c`b`&The latest poll:`0`b`c");
                poll_display();
            }
            if ($show_news == 1) {
                output("`c`b`&The latest news:`0`b`c");
                news_display();
            }
            if ($show_chat == 1) {
                chatroom();
            }
        }
        require_once ("lib/villagenav.php");
        villagenav();
        page_footer();
    }

function poll_display()
{
    //Currently don't utilise polls - if you're adding this to your site, feel free to submit a PR on GitHub!
}

function news_display()
{
    /* This function displays the latest news, however hasn't been validated yet.
    global $session;
    $sql = "SELECT newsid, news, author, postdate FROM " . db_prefix("news") . " ORDER BY newsid DESC LIMIT 1";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
    $newsid = $row['newsid'];
    $news = $row['news'];
    $author = $row['author'];
    $postdate = $row['postdate'];
    $sql = "SELECT name FROM " . db_prefix("accounts") . " WHERE acctid='$author'";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
    $author = $row['name'];
    $postdate = date("F j, Y, g:i a", strtotime($postdate));
    output("`c`b`&$news`0`b`c");
    output("`c`b`&Posted by $author on $postdate`0`b`c");
    */
}

function chatroom()
{
    $nav_name = get_module_setting("nav_name");
    require_once ("lib/commentary.php");
    addcommentary();
    commentdisplay("`c`b`&The $nav_name's chat window:`0`b`c", "market", "Speak amongst the crowd", 25, "says" );
}

function motd()
{
    global $session;
    $sql = "SELECT motdbody FROM " . db_prefix("motd") . " ORDER BY motddate DESC LIMIT 1";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
    $motd = $row['motdbody'];
    output_notl("`c`b`&$motd`0`b`c");
}

function market_runevent()
{
    return true;
}
?>
