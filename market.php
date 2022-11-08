<?php
/* The Market module by Tifa Zabat
An optional module for PHP versions of Legend of the Green Dragon.
This module adds a location called "The Market".
Allows users to have a space for OoC chatter without disturbing general roleplay.
Displays the latest MoTD, polls, and news, above the Market's chat window.
CC-BY-SA 4.0 github:tifasnow https://github.com/tifasnow/logd_market/
    */
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Autolink\AutolinkExtension;
use League\CommonMark\Extension\CommonMark\CommonMarkCoreExtension;
use League\CommonMark\Extension\CommonMark\Node\Inline\Link;
use League\CommonMark\Extension\DefaultAttributes\DefaultAttributesExtension;
use League\CommonMark\Extension\DescriptionList\DescriptionListExtension;
use League\CommonMark\Extension\DisallowedRawHtml\DisallowedRawHtmlExtension;
use League\CommonMark\Extension\Footnote\FootnoteExtension;
use League\CommonMark\Extension\SmartPunct\SmartPunctExtension;
use League\CommonMark\Extension\Strikethrough\StrikethroughExtension;
use League\CommonMark\Extension\Table\TableExtension;


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
                    tlschema($args['schemas']['marketnav']);
                    addnav($args['marketnav']);
                    addnav(get_module_setting("nav_name"),"runmodule.php?module=market&op=enter");
                    tlschema();
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
    // This function displays the latest news, if there is any.
    tlschema("news");
                output("`n`2`c`bLatest News`b`c");
                output("`2`c-=-=-=-=-=-=-=-`c");
                $sql = "SELECT newstext,arguments FROM " . db_prefix("news") . " ORDER BY newsid DESC LIMIT 1";
                $result = db_query($sql) or die(db_error(LINK));
                for ($i = 0; $i < 1; $i++) {
                    $row = db_fetch_assoc($result);
                    if ($row['arguments'] > "") {
                        $arguments = array();
                        $base_arguments = unserialize($row['arguments']);
                        array_push($arguments, $row['newstext']);
                        foreach ($base_arguments as $key => $val) {
                            array_push($arguments, $val);
                        }
                        $newnews = call_user_func_array("sprintf_translate", $arguments);
                    } else {
                        $newnews = $row['newstext'];
                    }
                    output("`c %s `c", $newnews);
                    if ($i <> 1) output("`2`c-=-=-=-=-=-=-=-`c");
                }
                output("`n");
                tlschema("user");
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
    $sql = "SELECT motdtitle, motddate, motdauthor, motdbody, name as motdauthorname FROM ".db_prefix("motd")." LEFT JOIN ".db_prefix("accounts")." ON ".db_prefix("accounts").".acctid = ".db_prefix("motd").".motdauthor ORDER BY motddate DESC LIMIT 1;";
    $result = db_query($sql);
    $row = db_fetch_assoc($result);
    $motd = $row['motdbody'];
    $motdtitle = $row['motdtitle'];
    $motdauthor = $row['motdauthorname'];
    $motddate = $row['motddate'];
    $environment = new Environment([
        'allow_unsafe_links' => false,
        'default_attributes' => [
            Link::class => [
                'class' => 'btn btn-link',
                'target' => '_blank',
            ],
        ],
    ]);

    $environment->addExtension(new CommonMarkCoreExtension());
    $environment->addExtension(new DefaultAttributesExtension());
    $converter = new \League\CommonMark\MarkdownConverter($environment);
    $environment->addExtension(new AutolinkExtension());
    $environment->addExtension(new DisallowedRawHtmlExtension());
    $environment->addExtension(new DescriptionListExtension());
    $environment->addExtension(new FootnoteExtension());
    $environment->addExtension(new SmartPunctExtension());
    $environment->addExtension(new StrikethroughExtension());
    $environment->addExtension(new TableExtension());

    output_notl("`b`^%s`0`b", $motdtitle);
    rawoutput(iconv("UTF-8",getsetting('charset','ISO-8859-1')."//TRANSLIT",$converter->convertToHtml(mb_convert_encoding(appoencode($motd."`n",true),"UTF-8"))));
    output_notl("`c`b`&Posted by %s on %s`0`b`c", $motdauthor, $motddate);

}

function market_runevent()
{
    return true;
}

?>
