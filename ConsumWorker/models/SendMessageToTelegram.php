<?php
/**
 * Created by PhpStorm.
 * User: Tim
 * Date: 11.09.2017
 * Time: 18:31
 */

namespace ConsumWorker;

use React\EventLoop\StreamSelectLoop;
use Bunny\Async\Client;
use Bunny\Channel;

include_once __DIR__ .'/../interfaces/WorkerInterface.php';

class SendMessageToTelegram implements WorkerInterface
{

    public function work(StreamSelectLoop $loop, array $aMessage)
    {
        //print_r($aMessage);
        $aLocation = $aMessage['location'];
        $aTelegramMessage = $aMessage['telegram_message'];
        $oTelegramConnector = new TelegramConnector($loop);
        $aPokemonNames = ["", "Bisasam", "Bisaknosp", "Bisaflor", "Glumanda", "Glutexo", "Glurak", "Schiggy", "Schillok", "Turtok", "Raupy", "Safcon", "Smettbo", "Hornliu", "Kokuna", "Bibor", "Taubsi", "Tauboga", "Tauboss", "Rattfratz", "Rattikarl", "Habitak", "Ibitak", "Rettan", "Arbok", "Pikachu", "Raichu", "Sandan", "Sandamer", "NidoranW", "Nidorina", "Nidoqueen", "NidoranM", "Nidorino", "Nidoking", "Piepi", "Pixi", "Vulpix", "Vulnona", "Pummeluff", "Knuddeluff", "Zubat", "Golbat", "Myrapla", "Duflor", "Giflor", "Paras", "Parasek", "Bluzuk", "Omot", "Digda", "Digdri", "Mauzi", "Snobilikat", "Enton", "Entoron", "Menki", "Rasaff", "Fukano", "Arkani", "Quapsel", "Quaputzi", "Quappo", "Abra", "Kadabra", "Simsala", "Machollo", "Maschock", "Machomei", "Knofensa", "Ultrigaria", "Sarzenia", "Tentacha", "Tentoxa", "Kleinstein", "Georok", "Geowaz", "Ponita", "Gallopa", "Flegmon", "Lahmus", "Magnetilo", "Magneton", "Porenta", "Dodu", "Dodri", "Jurob", "Jugong", "Sleima", "Sleimok", "Muschas", "Austos", "Nebulak", "Alpollo", "Gengar", "Onix", "Traumato", "Hypno", "Krabby", "Kingler", "Voltobal", "Lektrobal", "Owei", "Kokowei", "Tragosso", "Knogga", "Kicklee", "Nockchan", "Schlurp", "Smogon", "Smogmog", "Rihorn", "Rizeros", "Chaneira", "Tangela", "Kangama", "Seeper", "Seemon", "Goldini", "Golking", "Sterndu", "Starmie", "Pantimos", "Sichlor", "Rossana", "Elektek", "Magmar", "Pinsir", "Tauros", "Karpador", "Garados", "Lapras", "Ditto", "Evoli", "Aquana", "Blitza", "Flamara", "Porygon", "Amonitas", "Amoroso", "Kabuto", "Kabutops", "Aerodactyl", "Relaxo", "Arktos", "Zapdos", "Lavados", "Dratini", "Dragonir", "Dragoran", "Mewtu", "Mew", "Endivie", "Lorblatt", "Meganie", "Feurigel", "Igelavar", "Tornupto", "Karnimani", "Tyracroc", "Impergator", "Wiesor", "Wiesenior", "Hoothoot", "Noctuh", "Ledyba", "Ledian", "Webarak", "Ariados", "Iksbat", "Lampi", "Lanturn", "Pichu", "Pii", "Fluffeluff", "Togepi", "Togetic", "Natu", "Xatu", "Voltilamm", "Waaty", "Ampharos", "Blubella", "Marill", "Azumarill", "Mogelbaum", "Quaxo", "Hoppspross", "Hubelupf", "Papungha", "Griffel", "Sonnkern", "Sonnflora", "Yanma", "Felino", "Morlord", "Psiana", "Nachtara", "Kramurx", "Laschoking", "Traunfugil", "Icognito", "Woingenau", "Girafarig", "Tannza", "Forstellka", "Dummisel", "Skorgla", "Stahlos", "Snubbull", "Granbull", "Baldorfish", "Scherox", "Pottrott", "Skaraborn", "Sniebel", "Teddiursa", "Ursaring", "Schneckmag", "Magcargo", "Quiekel", "Keifel", "Corasonn", "Remoraid", "Octillery", "Botogel", "Mantax", "Panzaeron", "Hunduster", "Hundemon", "Seedraking", "Phanpy", "Donphan", "Porygon2", "Damhirplex", "Farbeagle", "Rabauz", "Kapoera", "Kussilla", "Elekid", "Magby", "Miltank", "Heiteira", "Raikou", "Entei", "Suicune", "Larvitar", "Pupitar", "Despotar", "Lugia", "Ho-Oh", "Celebi", "Geckarbor", "Reptain", "Gewaldro", "Flemmli", "Jungglut", "Lohgock", "Hydropi", "Moorabbel", "Sumpex", "Fiffyen", "Magnayen", "Zigzachs", "Geradaks", "Waumpel", "Schaloko", "Papinella", "Panekon", "Pudox", "Loturzel", "Lombrero", "Kappalores", "Samurzel", "Blanas", "Tengulist", "Schwalbini", "Schwalboss", "Wingull", "Pelipper", "Trasla", "Kirlia", "Guardevoir", "Gehweiher", "Maskeregen", "Knilz", "Kapilz", "Bummelz", "Muntier", "Letarking", "Nincada", "Ninjask", "Ninjatom", "Flurmel", "Krakeelo", "Krawumms", "Makuhita", "Hariyama", "Azurill", "Nasgnet", "Eneco", "Enekoro", "Zobiris", "Flunkifer", "Stollunior", "Stollrak", "Stolloss", "Meditie", "Meditalis", "Frizelbliz", "Voltenso", "Plusle", "Minun", "Volbeat", "Illumise", "Roselia", "Schluppuck", "Schlukwech", "Kanivanha", "Tohaido", "Wailmer", "Wailord", "Camaub", "Camerupt", "Qurtel", "Spoink", "Groink", "Pandir", "Knacklion", "Vibrava", "Libelldra", "Tuska", "Noktuska", "Wablu", "Altaria", "Sengo", "Vipitis", "Lunastein", "Sonnfel", "Schmerbe", "Welsar", "Krebscorps", "Krebutack", "Puppance", "Lepumentas", "Liliep", "Wielie", "Anorith", "Armaldo", "Barschwa", "Milotic", "Formeo", "Kecleon", "Shuppet", "Banette", "Zwirrlicht", "Zwirrklop", "Tropius", "Palimpalim", "Absol", "Isso", "Schneppke", "Firnontor", "Seemops", "Seejong", "Walraisa", "Perlu", "Aalabyss", "Saganabyss", "Relicanth", "Liebiskus", "Kindwurm", "Draschel", "Brutalanda", "Tanhel", "Metang", "Metagross", "Regirock", "Regice", "Registeel", "Latias", "Latios", "Kyogre", "Groudon", "Rayquaza", "Jirachi", "Deoxys", "Chelast", "Chelcarain", "Chelterrar", "Panflam", "Panpyro", "Panferno", "Plinfa", "Pliprin", "Impoleon", "Staralili", "Staravia", "Staraptor", "Bidiza", "Bidifas", "Zirpurze", "Zirpeise", "Sheinux", "Luxio", "Luxtra", "Knospi", "Roserade", "Koknodon", "Rameidon", "Schilterus", "Bollterus", "Burmy", "Burmadame", "Moterpel", "Wadribie", "Honweisel", "Pachirisu", "Bamelin", "Bojelin", "Kikugi", "Kinoso", "Schalellos", "Gastrodon", "Ambidiffel", "Driftlon", "Drifzepeli", "Haspiror", "Schlapor", "Traunmagil", "Kramshef", "Charmian", "Shnurgarst", "Klingplim", "Skunkapuh", "Skuntank", "Bronzel", "Bronzong", "Mobai", "Pantimimi", "Wonneira", "Plaudagei", "Kryppuk", "Kaumalat", "Knarksel", "Knakrack", "Mampfaxo", "Riolu", "Lucario", "Hippopotas", "Hippoterus", "Pionskora", "Piondragi", "Glibunkel", "Toxiquak", "Venuflibis", "Finneon", "Lumineon", "Mantirps", "Shnebedeck", "Rexblisar", "Snibunna", "Magnezone", "Schlurplek", "Rihornior", "Tangoloss", "Elevoltek", "Magbrant", "Togekiss", "Yanmega", "Folipurba", "Glaziola", "Skorgro", "Mamutel", "Porygon-Z", "Galagladi", "Voluminas", "Zwirrfinst", "Frosdedje", "Rotom", "Selfe", "Vesprit", "Tobutz", "Dialga", "Palkia", "Heatran", "Regigigas", "Giratina", "Cresselia", "Phione", "Manaphy", "Darkrai", "Shaymin", "Arceus", "Victini", "Serpifeu", "Efoserp", "Serpiroyal", "Floink", "Ferkokel", "Flambirex", "Ottaro", "Zwottronin", "Admurai", "Nagelotz", "Kukmarda", "Yorkleff", "Terribark", "Bissbark", "Felilou", "Kleoparda", "Vegimak", "Vegichita", "Grillmak", "Grillchita", "Sodamak", "Sodachita", "Somniam", "Somnivora", "Dusselgurr", "Navitaub", "Fasasnob", "Elezeba", "Zebritz", "Kiesling", "Sedimantur", "Brockoloss", "Fleknoil", "Fletiamo", "Rotomurf", "Stalobor", "Ohrdoch", "Praktibalk", "Strepoli", "Meistagrif", "Schallquap", "Mebrana", "Branawarz", "Jiutesto", "Karadonis", "Strawickl", "Folikon", "Matrifol", "Toxiped", "Rollum", "Cerapendra", "Waumboll", "Elfun", "Lilminip", "Dressella", "Barschuft", "Ganovil", "Rokkaiman", "Rabigator", "Flampion", "Flampivian", "Maracamba", "Lithomith", "Castellith", "Zurrokex", "Irokex", "Symvolara", "Makabaja", "Echnatoll", "Galapaflos", "Karippas", "Flapteryx", "Aeropteryx", "Unrat&uuml;tox", "Deponitox", "Zorua", "Zoroark", "Picochilla", "Chillabell", "Mollimorba", "Hypnomorba", "Morbitesse", "Monozyto", "Mitodos", "Zytomega", "Piccolente", "Swaroness", "Gelatini", "Gelatroppo", "Gelatwino", "Sesokitz", "Kronjuwild", "Emolga", "Laukaps", "Cavalanzas", "Tarnpignon", "Hutsassa", "Quabbel", "Apoquallyp", "Mamolida", "Wattzapf", "Voltula", "Kastadur", "Tentantel", "Klikk", "Kliklak", "Klikdiklak", "Zapplardin", "Zapplalek", "Zapplarang", "Pygraulon", "Megalon", "Lichtel", "Laternecto", "Skelabra", "Milza", "Sharfax", "Maxax", "Petznief", "Siberio", "Frigometri", "Schnuthelm", "Hydragil", "Flunschlik", "Lin-Fu", "Wie-Shu", "Shardrago", "Golbit", "Golgantes", "Gladiantri", "Caesurio", "Bisofank", "Geronimatz", "Washakwil", "Skallyk", "Grypheldis", "Furnifrass", "Fermicula", "Kapuno", "Duodino", "Trikephalo", "Ignivor", "Ramoth", "Kobalium", "Terrakium", "Viridium", "Boreos", "Voltolos", "Reshiram", "Zekrom", "Demeteros", "Kyurem", "Keldeo", "Meloetta", "Genesect", "Igamaro", "Igastarnish", "Brigaron", "Fynx", "Rutena", "Fennexis", "Froxy", "Amphizel", "Quajutsu", "Scoppel", "Grebbit", "Dartiri", "Dartignis", "Fiaro", "Purmel", "Puponcho", "Vivillon", "Leufeo", "Pyroleo", "Flab&eacute;b&eacute;", "FLOETTE", "Florges", "M&auml;hikel", "Chevrumm", "Pam-Pam", "Pandagro", "Coiffwaff", "Psiau", "Psiaugon", "Gramokles", "Duokles", "Durengard", "Parfi", "Parfinesse", "Flauschling", "Sabbaione", "Iscalar", "Calamanero", "Bithora", "Thanathora", "Algitt", "Tandrak", "Scampisto", "Wummer", "Eguana", "Elezard", "Balgoras", "Monargoras", "Amarino", "Amagarga", "Feelinara", "Resladero", "DEDENNE", "Rocara", "Viscora", "Viscargot", "Viscogon", "Clavion", "Paragoni", "Trombork", "Irrbis", "Pumpdjinn", "Arktip", "Arktilas", "eF-eM", "UHaFnir", "Xerneas", "Yveltal", "Zygarde", "Diancie", "Hoopa", "Volcanion"];
        
	echo date("h:i:s", time())." - SendMessageToTelegram - ".$aLocation['chat_id']." - ".$aTelegramMessage['pokemon_info']['pokemon_id']." - ".$aTelegramMessage['pokemon_info']['disappear_time'].PHP_EOL;
	$aParam = [
            'chat_id' => $aLocation['chat_id'],
            'photo' => POKELYTICS_IMAGE_LOCATION.$aTelegramMessage['pokemon_info']['pokemon_id'].'.png',
            'caption' => urlencode($aPokemonNames[$aTelegramMessage['pokemon_info']['pokemon_id']] . (isset($aTelegramMessage['pokemon_info']['iv']) ? ' - '.$aTelegramMessage['pokemon_info']['iv'] :'') . ' - ' .  $aTelegramMessage['address'] . ' - ' .date("H:i:s",$aTelegramMessage['pokemon_info']['disappear_time']) . ' - https://maps.google.com/?daddr=' . $aTelegramMessage['pokemon_info']['latitude'] . ',' . $aTelegramMessage['pokemon_info']['longitude'])
        ];
        
        $oTelegramConnector->getContent($aParam)->then(
            function($result) use ($aLocation) {
                if($result['ok']){
                    echo date("h:i:s", time())." - TelegramSuccess - ".$aLocation['chat_id']." - ".$result['ok'].PHP_EOL;
                } else {
                    echo date("h:i:s", time())." - TelegramError - ".$aLocation['chat_id']." - ".$result['ok']." - ".$result['description']." - ".$result['error_code'].PHP_EOL;
                }
            }
        );        
    }
}
