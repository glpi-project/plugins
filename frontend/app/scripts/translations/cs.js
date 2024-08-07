'use strict';

angular
   .module('frontendApp')
   .config(function($translateProvider) {
      $translateProvider
         .translations('cs', {
            ON: "on",
            SLOGAN: "Rozšiřte si GLPI pomocí zásuvných modulů",
            TRENDING: "Nyní v kurzu",
            TRENDING_SUB: "Oblíbené v tomto měsíci",
            NEW: "Nové",
            NEW_SUB: "Nejnovější v katalogu",
            POPULAR: "Oblíbené",
            POPULAR_SUB: "S nejvíce instalacemi (duplicity odfiltrovány)",
            UPDATED: "Aktualizováno",
            UPDATED_SUB: "Nedávno aktualizované zásuvné moduly",
            TAGS: "Štítky",
            TAGS_SUB: "S nejvyšším počtem zásuvných modulů",
            AUTHORS: "Autoři",
            AUTHORS_SUB: "S nejvyšším počtem příspěvků",
            NAV_FEATURED: "Doporučované",
            NAV_ALLPLUGINS: "Všechny zásuvné moduly",
            NAV_SEARCH: "Hledat",
            NAV_SUBMIT_A_PLUGIN: "Nahrát zásuvný modul",
            NAV_CONTACT: "Kontakt",
            NAV_DEVELOPER_GUIDES: "Příručky pro vývojáře (API)",
            SEARCHBAR_PLACEHOLDER: "Hledat",
            VERSION: "Verze",
            COMPATIBLE_WITH: "Kompatibilní s",
            HOMEPAGE: "Domovská stránka",
            ADDED: "Přidáno",
            BY: "Od",
            CONTRIBUTED_TO: "přispěl(a) do {{plugincount}} zásuvných modulů",
            PLUGIN_CONTRIBUTED_TO: "přispěl(a) do / pracoval(a) na těchto zásuvných modulech",
            TAGGED_WITH: "oštítkováno",
            PLUGIN_TAGGED_WITH: "Zásuvné moduly se štítkem",
            QUESTION_SUGGESTION_PROBLEM: "Nějaký dotaz, doporučení či problém?",
            PLEASE_SEND_US_A_MESSAGE: "Pošlete nám zprávu.",
            PLEASE_USE_SUGGESTION_TRACKER: "Pokud je vaše zpráva požadavkem na přidání funkce do GLPI, vřele doporučujeme použít systém správy doporučení na",
            FIRSTNAME: "Jméno",
            LASTNAME: "Příjmení",
            EMAIL: "E-mail",
            SUBJECT: "Předmět",
            YOUR_MESSAGE: "Vaše zpráva",
            SUBMIT: "Nahrát",
            SUBMIT_YOUR_PLUGIN: "Nahrajte svůj zásuvný modul!",
            SUB_SUBMIT_YOUR_PLUGIN: "Zadejte URL adresu popisující váš zásuvný modul.",
            GLPI_PLUGIN_CREATORS: "Tvůrci zásuvných modulů pro GLPI",
            PAGINATION_RESULTS: "Výsledky",
            PAGINATION_TO: "až",
            PAGINATION_ON: "z",
            PAGINATION_ELEMENTS: "prvky",
            NO_RESULTS: "Žádný výsledek",
            NEVER_UPDATED: "od okamžiku prvního zveřejnění nikdy neaktualizováno",
            LOADING: "Načítání",
            LOGIN: "Přihlásit",
            USER_PANEL: "Panel uživatele",
            DISCONNECT: "Odhlásit",
            SIGNIN: "Přihlásit",
            SIGNUP: "Zaregistrovat",
            INVALID_CREDENTIALS: "Zadány neplatné přihlašovací údaje. Pokud si nejste jistí heslem, klikněte na „Zapoměl(a) jsem své heslo“",
            FIELD_REQUIRED: "* Vyžadováno",
            EXTERNAL_ACCOUNT_ALREADY_PAIRED: "Tento externí účet už byl propojen s jiným účtem na GLPI Plugins.",
            PLUGIN_ALREADY_WATCHED: "Dění okolo tohoto zásuvného modulu už sledujete",
            YOURE_NOW_WATCHING: "Nyní sledujete dění",
            PLUGIN_UNWATCHED: "Přestali jste sledovat dění",
            TAGGED: "Oštítkováno",
            DOWNLOADED: "Staženo",
            THIS_MONTH: "Tento měsíc",
            THIS_WEEK: "Tento týden",
            TRANSLATIONS: "Přeloženo do",
            XMLFILE_UNREACHABLE_URL: 'Nedosažitelná URL adresa',
            XMLFILE_PARSE_ERROR: 'Chyba při zpracovávání XML',
            XMLFILE_SPECIFICATION_ERROR: 'Chyba specifikace',
            PANEL_DECLARE_NEW_OAUTH2_APP: 'Deklarovat novou OAuth2 aplikaci',
            PANEL_REGISTER_NEW_APP: 'Zaregistrovat novou aplikaci pro získání přístupu k API',
            PANEL_APPLICATION_NAME: 'Název aplikace',
            PANEL_HOMEPAGE_URL: 'URL adresa domovské stránky (volitelné)',
            PANEL_APP_DESCRIPTION: 'Popis aplikace (volitelné, příklad „API klíč pro mujskript/mujjazyk“)',
            PANEL_APP_CREATE: 'Vytvořit',
            PANEL_MY_APPLICATIONS: 'Moje aplikace',
            PANEL_EDIT_APP_SETTINGS: 'Upravit nastavení své aplikace',
            PANEL_SETTINGS: 'Nastavení',
            PANEL_CLIENT_ID: 'Identifikátor klienta',
            PANEL_CLIENT_SECRET: 'Heslo klienta',
            PANEL_APPLICATION_NAME_TOO_SHORT: 'Je třeba, aby název vaší aplikace byl alespoň {{n}} znaků dlouhý',
            PANEL_APPLICATION_NAME_TOO_LONG: 'Je třeba, aby délka názvu vaší aplikace nepřesahovala {{n}} znaků',
            PANEL_APP_HOMEPAGE_URL: 'URL domovské stránky (volitelné)',
            PANEL_HOMEPAGE_URL_NOT_VALID: 'Toto není platná URL adresa',
            PANEL_APP_DESCRIPTION_TOO_LONG: 'Je třeba, aby délka popisu vaší aplikace nepřesahovala {{n}} znaků',
            PANEL_APP_SAVE: 'Uložit',
            PANEL_APP_DELETE: 'Smazat tuto aplikaci',
            AUTHOR_NAME: 'Jméno',
            AUTHOR_GLPI_PLUGINS_USERNAME: 'Uživatelské jméno na GLPi Plugins',
            AUTHOR_CONTRIBUTED_TO: 'Přispěl(a) do',
            AUTHOR_PLUGIN_COUNT: '{{plugincount}} zásuvných modulů',
            PANEL_CLAIM_AN_AUTHORSHIP: 'Prohlásit se za autora',
            PANEL_CLAIM_AN_AUTHORSHIP_FEATURE_DESC: 'Po nahrání zásuvného modulu prostřednictvím XML můžete prohlásit, že jste konkrétní autor.',
            PANEL_CLAIM_AN_AUTHORSHIP_FEATURE_DESC2: 'Pokud požadavek přijmeme (tj. ujistíme se, že jste skutečně autor), budou vám přiřazeny všechny vaše zásuvné moduly a udělena konkrétní práva pro správu',
            PANEL_CLAIM_AN_AUTHORSHIP_AUTHOR_NAME: 'Jméno autora (přesně jak je zmíněno ve štítku &lt;autor&gt; v XML souborech zásuvných modulů, kterých se týká)',
            PANEL_CLAIM_AN_AUTHORSHIP_CLAIM: 'Prohlásit',
            PANEL_DELETE_ACCOUNT: 'Smazat váš GLPi Plugins účet',
            PANEL_DELETE_ACCOUNT_CONFIRM: 'Smazání účtu na GLPI Plugins potřebujeme potvrdit zadáním vašeho hesla.',
            PANEL_DELETE_ACCOUNT_PASSWORD: 'Heslo',
            PANEL_DELETE_ACCOUNT_DELETE: 'Smazat účet',
            DOWNLOADED_N_TIMES: 'Staženo {{n}} krát',
            ADDED_ON_DATE: 'Přidáno {{date}}',
            UPDATED_MOMENTS_AGO: 'Aktualizováno {{momentsago}}',
            ADDED_MOMENTS_AGO: 'Pridáno {{momentsago}}',
            TAGGED_ON_N_PLUGINS: 'Štítkem označeno {{plugincount}} zásuvných modulů',
            CONTRIBUTED_TO_N_PLUGINS: 'Přispěl(a) do {{plugincount}} zásuvných modulů',
            FINISHACTIVEACCOUNT_CLOSE_TO_THE_GOAL: 'Jste blízko cíle',
            FINISHACTIVATEACCOUNT_WE_NEED_YOU_TO_VALIDATE_SOME_DATA: 'Potřebujeme ověřit některé údaje',
            FINISHACTIVEACCOUNT_CONFIRM_USERNAME: 'Potvrďte (nebo změňte) své uživatelské jméno',
            FINISHACTIVATEACCOUNT_SELECT_PRIMARY_EMAIL: 'Vyberte hlavní e-mail z jednoho ze svých externích účtů',
            FINISHACTIVEACCOUNT_CONFIRM: 'Potvrdit',
            LINKACCOUNT_AN_EXTERNAL_SOCIAL_ACCOUNT: 'Propojit externí účet ze sociálních sítí',
            LINKACCOUNT_LINK_A_NEW_ACCOUNT: 'Propojit nový účet',
            LINKACCOUNT_EXTERNAL_ACCOUNTS: 'Vaše externí účty',
            LINKACCOUNT_SERVICE: 'Služba',
            LINKACCOUNT_USERID: 'Identifikátor uživatele',
            LINKACCOUNT_UNLINK: 'Odpojit',
            NOTIFICATIONS_PLUGINS_WATCHED: 'Zásuvné moduly, okolo kterých sledujete dění',
            NOTIFICATIONS_YOURE_NOT_WATCHING_ANY_PLUGINS: 'V tuto chvíli nesledujete dění okolo žádného ze zásuvných modulů',
            NOTIFICATIONS_YOULL_RECEIVE_NOTIFICATIONS_ON_UPDATE: 'Obdržíte oznámení o aktualizacích',
            NOTIFICATIONS_UNWATCH: 'přestat sledovat dění',
            NOTIFICATIONS_DISCOVER_AND_SUBSCRIBE: 'Ze stránek zásuvného modulu také můžete objevovat nové a přihlásit se k odběru oznámení',
            NOTIFICATIONS_PLEASE_CHECK_THOSE_TRENDING_PLUGINS: 'Podívejte se na tyto zásuvné moduly, které jsou v kurzu',
            PANEL_MY_INFORMATIONS: 'Údaje pro mne',
            PANEL_USERNAME: 'Uživatelské jméno',
            PANEL_USERNAME_TOOSHORT: 'Je třeba, aby uživatelské jméno bylo alespoň {{n}} znaků dlouhé',
            PANEL_USERNAME_TOOLONG: 'Délka uživatelského jména nemůže překročit {{n}} znaků',
            PANEL_REALNAME: 'Skutečné jméno',
            PANEL_REALNAME_TOOSHORT: 'Je třeba, aby skutečné jméno bylo alespoň {{n}} znaků dlouhé',
            PANEL_REALNAME_TOOLONG: 'Délka skutečného jména nemůže překročit {{n}} znaků',
            PANEL_EMAIL: 'E-mail',
            PANEL_FIELD_REQUIRED: 'Kolonku je třeba vyplnit',
            PANEL_INVALID_EMAIL: 'Toto není platná e-mailová adresa',
            PANEL_WEBSITE: 'Webová stránka',
            PANEL_INVALID_WEBSITE: 'Toto není platná URL adresa webové stránky',
            PANEL_PASSWORD: 'Heslo',
            PANEL_PASSWORD_CONFIRMATION_DIFFERENT: 'Heslo a jeho zadání pro potvrzení si neodpovídají',
            PANEL_PASSWORD_TOOSHORT: 'Je třeba, aby heslo bylo alespoň {{n}} znaků dlouhé',
            PANEL_PASSSWORD_TOOLONG: 'Je třeba, aby délka hesla nepřesáhla {{n}} znaků',
            PANEL_CONFIRM_PASSWORD: 'Potvrzení hesla',
            PANEL_UPDATE: 'Aktualizovat',
            PANEL_ACTIONS: 'Akce',
            PANEL_MANAGE_EXTERNAL_SOCIAL_ACCOUNTS: 'Spravovat mé externí účty ze sociálních sítí',
            PANEL_NOTIFICATIONS_SETTINGS: 'Nastavení oznamování',
            PANEL_MANAGE_API_KEYS: 'Spravovat API klíče (a související aplikace)',
            PANEL_PLEASE_DELETE_MY_ACCOUNT: 'Prosím smažte můj účet',
            PANEL_MY_PLUGINS: 'Mé zásuvné moduly',
            PANEL_AWAITING_CONFIRMATION: 'Čeká na potvrzení',
            PANEL_UNREACHABLE_XML_FILE_URL: 'Nedosažitelná URL adresa XML souboru',
            PANEL_INVALID_XML: 'Neplatné XML',
            PANEL_DELETE_RELATION_TO_PLUGINS: 'Smazat svou spojitost s tímto zásuvným modulem',
            PANEL_MANAGE_PERMISSIONS: 'Spravovat oprávnění',
            PANEL_RELOAD_XML: 'Znovu načíst XML',
            PANEL_PLUGIN_PANEL: 'Panel zásuvného modulu',
            N_VOTES: '{{n}} hlasů',
            DOWNLOADS: 'stažení',
            DOWNLOAD: 'Stáhnout',
            PLUGIN_UNWATCH: 'Přestat sledovat',
            PLUGIN_WATCH: 'Sledovat',
            PLUGIN_ISSUES: 'Hlášení chyb',
            SORT_BY: 'Řadit podle',
            RELEVANCE: 'Relevance',
            POPULARITY: 'Oblíbenosti',
            PLUGIN_PANEL: 'Panel zásuvného modulu',
            PLUGIN_PANEL_SUMMARY: 'Souhrn',
            PLUGIN_PANEL_XML_STATE: 'Stav XML',
            PLUGIN_PANEL_N_TIMES: '{{n}} krát',
            PLUGIN_PANEL_CONTRIBUTORS: 'Přispěvatelé (Uvedení v XML souboru)',
            PLUGIN_PANEL_SETTINGS: 'Nastavení',
            PLUGIN_PANEL_XML_URL: 'URL adresa souboru s autoritativním meta-popisem (XML soubor, viz specifikace)',
            PLUGIN_PANEL_ACTIONS: 'Akce',
            PLUGIN_PANEL_REFRESH_XML_FILE: 'ZNovu načíst XML soubor',
            PLUGIN_PANEL_USER_PERMISSIONS: 'Spravovat oprávnění uživatele',
            PLUGIN_PANEL_URLNOTREACHABLE: 'URL adresa {{url}} není dosažitelná.',
            PLUGIN_PANEL_FIELD_FAILS_TO_RESPECT_SPEC: 'Kolonka {{field}} neodpovídá specifikaci',
            PLUGIN_PANEL_ERROR_AT_LINE_N: 'Chyba na řádku {{n}}',
            PLUGIN_PANEL_SEE_PUBLIC_PAGE: 'Viz veřejná stránka',
            PLUGIN_PANEL_SAVE: 'Uložit',
            PLUGIN_PERMISSIONS_OF_PLUGIN: 'Spravovat oprávnění uživatelů GLPi Plugins na zásuvném modulu „{{pluginkey}}“ ',
            PLUGIN_PERMISSIONS_USER_PERMISSIONS: 'Uživatelská oprávnění',
            PLUGIN_PERMISSIONS_USER_PERMISSIONS_DETAILS_1: 'Na řádku každého z oprávnění jsou kolonky pro každé možné oprávnění které můžete udělit uživateli GLPi Plugins na zásuvném modulu.',
            PLUGIN_PERMISSIONS_USER_PERMISSIONS_DETAILS_2: 'Stačí jen zaškrtnout nebo zrušit zaškrtnutí kolonek a okamžitě je přidáno/odebráno oprávnění, pro řádek oprávnění a uživatele.',
            PLUGIN_PERMISSIONS_USER_PERMISSIONS_DETAILS_3: 'Až dokončíte správu oprávnění, dialog zavřete.',
            PLUGIN_PERMISSIONS_USER_PERMISSIONS_DETAILS_4: 'Pro úplné odebrání řádku pro daného uživatele použijte červené tlačítko s křížkem, pro všechny jeho/její oprávnění na zásuvném modulu',
            PLUGIN_PERMISSIONS_ADMIN: 'Správce',
            PLUGIN_PERMISSIONS_ALLOWED_EVERYTHING: 'Umožněno vše',
            PLUGIN_PERMISSIONS_REFRESH_XML: 'Umožněno znovu načíst synchronizaci s XML souborem',
            PLUGIN_PERMISSIONS_RECEIVE_NOTIFICATIONS: 'Obdržet oznámení o stavu průchodu XML',
            PLUGIN_PERMISSIONS_ALLOWED_CHANGE_XML_URL: 'Umožněno změnit URL adresu XML',
            PLUGIN_PERMISSIONS_DELETE_USER_RIGHT: 'Smazat toto oprávnění uživatele',
            PLUGIN_PERMISSIONS_ADD_USER_PERMISSIONS: 'Přidat uživatelská oprávnění',
            PLUGIN_PERMISSIONS_AUTOCOMPLETE_PLACEHOLDER: 'Zadejte uživatelské jméno, skutečné jméno nebo e-mail zaregistrovaného uživatele, pak pomocí šipky vstupte do výběru uživatele',
            PLUGIN_PERMISSIONS_AUTOCOMPLETE_NO_MATCHING_USERS: 'Žádný shodující se uživatel.',
            SIGNIN_PAGE_TITLE: 'Přihlášení GLPI Plugins účtem',
            SIGNIN_USERNAME_OR_EMAIL: 'Uživatelské jméno nebo E-mail',
            SIGNIN_PASSWORD: 'Heslo',
            SIGNIN_LOGIN: 'Přihlášení',
            SIGNIN_OR_CONNECT_WITH_AN_EXTERNAL_ACCOUNT: 'Nebo se přihlaste pomocí externího účtu',
            SIGNUP_REGISTER_MANUALLY: 'Zaregistrovat ručně',
            SIGNUP_USERNAME: 'Uživatelské jméno',
            SIGNUP_USERNAME_TOOSHORT: 'Je třeba, aby uživatelské jméno bylo alespoň {{n}} znaků dlouhé',
            SIGNUP_USERNAME_TOOLONG: 'Délka uživatelského jména nemůže přesáhnout {{n}} znaků',
            SIGNUP_USERNAME_BADCHARACTERS: 'Uživatelské jméno by mělo obsahovat pouze písmena (latinka) a číslice',
            SIGNUP_REALNAME: 'Skutečné jméno',
            SIGNUP_REALNAME_TOOSHORT: 'Je třeba, aby skutečné jméno bylo alespoň {{n}} znaků dlouhé',
            SIGNUP_REALNAME_TOOLONG: 'Délka skutečného jména nemůže přesáhnout {{n}} znaků',
            SIGNUP_EMAIL: 'E-mail',
            SIGNUP_EMAIL_INVALID: 'Toto není platný e-mail',
            SIGNUP_WEBSITE: 'Webové stránky',
            SIGNUP_WEBSITE_INVALID: 'Toto není platný web',
            SIGNUP_PASSWORD: 'Heslo',
            SIGNUP_PASSWORD_DIFFERENT: 'Heslo a potvrzení jeho zadání se neshodují',
            SIGNUP_PASSWORD_TOOSHORT: 'Je třeba, aby heslo bylo alespoň {{n}} znaků dlouhé',
            SIGNUP_PASSWORD_TOOLONG: 'Délka hesla nemůže překročit {{n}} znaků',
            SIGNUP_PASSWORD_CONFIRM: 'Potvrdit heslo',
            SIGNUP_OR_LINK_YOUR_ACCOUNT: 'Nebo propojte svůj účet',
            SUBMIT_PLUGIN_URL: 'URL adresa',
            SUBMIT_YOU_MUST_BE_AUTHED: 'Je třeba, aby byla ověřena vaše totožnost',
            SUBMIT_YOU_MUST_BE_AUTHED_2: 'Abyste mohli nahrát zásuvný modul, je třeba, aby byla ověřena vaše totožnost',
            SUBMIT_PLEASE: 'Prosíme',
            SUBMIT_SIGNIN: 'přihlaste se',
            SUBMIT_IF_YOU_DONT_HAVE_AN_ACCOUNT: 'Pokud ještě nemáte GLPi Plugins účet, zvažte',
            SUBMIT_REGISTER: 'registraci',
            SUBMIT_YOUR_XML_MUST_BE_VALID: 'Je třeba, aby vaše XML bylo platné',
            SUBMIT_AND_RESPECT_THIS_FORMAT: 'a byl respektován jeho formát',
            SUBMIT_XML_DISPLAYED_NAME: 'Zobrazovaný název',
            SUBMIT_XML_KEY: 'Systémový název',
            SUBMIT_XML_SHORT_DESCRIPTION: 'stručný popis zásuvného modulu, zobrazovaný v seznamu modulů (pouze text)',
            SUBMIT_XML_LONG_DESCRIPTION: 'podrobný popis zásuvného modulu, zobrazovaný v kartách zásuvného modulu (je možné používat Markdown formátovací značky)',
            SUBMIT_XML_YOUR_NAME: 'Vaše jméno',
            TAGS_TAGGED_ON_N_PLUGINS: 'Označeno na {{n}} zásuvných modulech',
            VIEWAPIKEY_PAGE_TITLE: 'Zobrazit přihlašovací údaje klienta',
            VIEWAPIKEY_CLIENT_CREDENTIALS: 'Přihlašovací údaje klienta',
            VIEWAPIKEY_CLIENT_ID: 'Identifikátor klienta',
            VIEWAPIKEY_CLIENT_SECRET: 'Heslo klienta',
            INVALID_XML_BECAUSE_UNREACHABLE_URL: 'Váš XML soubor není dosažitelný na URL, kterou jste zmínili',
            README: 'DOKUMENTACE',
            FORGOTPASSWORD_DIALOG_TITLE: 'Zapoměli jste své heslo',
            FORGOTPASSWORD_DIALOG_FORMINTRO_1: "Pokud jste zde, nejspíš jste ztratili své heslo.",
            FORGOTPASSWORD_DIALOG_FORMINTRO_2: "Je třeba, abyste zadali e-mail svého účtu.",
            FORGOTPASSWORD_DIALOG_FORMINTRO_3: "Mějte na paměti, že pokud jste předtím ještě nikdy nenastavovali heslo (protože jste se vždy přihlašovali prostřednictvím Github) pak není možné heslo obnovit, protože nikdy nebylo nastavené. Měli byste tento dialog zavřít a ověřit se prostřednictvím Github.",
            SEND_MAIL_PASSWORD_RESET_LINK: "Poslat odkaz na resetování hesla e-mailem",
            CHANGELOG: 'Changelog',
         });
   });
