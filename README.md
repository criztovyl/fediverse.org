#Important Fork Notes.

## Existing Repos

This fork removed the SQLite databases from Git, so don't do a `git pull`
into an upstream repository!

You can still pull this repo, but will need to back-up your databases
beforehand and restore them afterwards:

    cd data/dbs

    # Backup
    # Copy to keep git silent about changed/moved files
    cp fedib.db{,.bak}
    cp geodb.db{,.bak}

    git pull

    # Restore
    mv fedib.db{.bak,}
    mv geodb.db{.bak,}

## PHP Version

`fediverse.org` requires PHP >= 5.6 or PHP 7. If you think a lower PHP version
is better, please open an Issue.

## GeoIP

The GeoIP link below is broken, and I did not yet take time to look into it.

Fediverse.org
=============

 
 Fediverse.org is an attempt to map all public nodes that are part of the GNU
 social Fediverse, in order to contribute to the growth of the community. We
 offer a neutral starting point for newcomers, while also being a knowledge base
 for admins and existing users.
 
## Important information
 
 In order for this to work, you need to add
 [this GeoIP database](https://mega.nz/#!oEoTAQKa!82TEvs1XP-BFF_Rplqhw09DGnWJ7xkGch0mtd2C3FMQ)
 I created in the `./data/dbs/` directory. Unfortunately, it's more than 100 MB
 and GitHub thinks it's an ugly file. Shame on you, GitHub!
 
 [MaxMind](https://www.maxmind.com/en/home) originally provided the information
 in the database.
 
## How it works
 
 It's actually very simple.
 
 There is a frontend which includes a pre-crawled table of the main nodes that
 are part of the !fediverse. It also handles the UI, submitting new nodes, etc.
 
 The database is refreshed every 15 minutes by the parser, which is executed via
 cronjob by the `./fediadmin.php`. This parser generates an HTML table with all
 nodes and updates the main SQLite database. `./fediadmin.php` also includes
 other methods like `generate-table`, which regenerates the HTML table without
 crawling all nodes for new information. It can be executed via command line
 (which is recommended), but unfortunately the server where the site is
 currently hosted doesn't allow commands in my cronjobs, so crawling is done via
 a simple wget.
 
 More information can be found [on the site itself](http://www.fediverse.org/).
 I promise to make a more useful README file here on Github later.
 
 This is a community-supported, non-profit project. Feel free to collaborate or
 donate if you want to contribute! Please visit the
 [TODO list](http://www.fediverse.org/to-do) of pending tasks and suggestions
 for a better idea of how you can contribute.
