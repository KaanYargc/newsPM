<?php

declare(strict_types=1);

namespace yargc\news;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\player\Player;
use pocketmine\plugin\PluginBase;
use yargc\news\Vecnavium\FormsUI\SimpleForm;
use yargc\news\Vecnavium\FormsUI\CustomForm;

class Main extends PluginBase
{
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool
    {
        if ($command->getName() === "news") {
            if ($sender instanceof Player) {
                $this->showNewsList($sender);
            } else {
                $sender->sendMessage("This command can only be used in-game.");
            }
        }
        return true;
    }

    public function showNewsList(Player $player): void
    {
        $url = "https://www.trthaber.com/xml_mobile.php?tur=xml_genel&selectEx=okunmaadedi,yorumSay&id=HABERID_BURAYA_GELECEK&commentList=show";
        $xmlContent = file_get_contents($url);
        $xml = simplexml_load_string($xmlContent);

        $form = new SimpleForm(function (Player $player, ?int $data) use ($xml) : void {
            if ($data !== null) {
                $this->showNewsDetail($player, $xml->haber[$data]);
            }
        });

        $form->setTitle("Haberler");

        foreach ($xml->haber as $index => $haber) {
            $title = strip_tags((string) $haber->haber_manset);
            $description = strip_tags(substr((string) $haber->haber_aciklama, 0, 100)) . '...';
            $image = (string) $haber->haber_resim;
            $form->addButton("$title\n$description", SimpleForm::IMAGE_TYPE_URL, $image);
        }

        $player->sendForm($form);
    }

    public function showNewsDetail(Player $player, $haber): void
    {
        $form = new CustomForm(function (Player $player, array $data) : bool {
            return true;
        });

        $title = strip_tags((string) $haber->haber_manset);
        $content = strip_tags((string) $haber->haber_metni);
        $image = (string) $haber->haber_resim;

        $form->setTitle($title);
        $form->addLabel($content);
        $form->addLabel("Resim: $image");

        $player->sendForm($form);
    }
}