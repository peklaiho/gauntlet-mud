# Gauntlet MUD

Gauntlet MUD is an engine for [MUDs](https://en.wikipedia.org/wiki/Multi-user_dungeon) written in PHP.

## Data

To run Gauntlet MUD, you need to pair it with a data directory that contains YAML files for rooms, items, monsters and so forth.

Example of the data directory structure will be made available later, but unfortunately it has not been published yet. The urgency for this effort will depend on how much interest there is for it. Meanwhile, you can figure out the data directory structure by looking at the classes in the `src/Data` directory which read the YAML files.

## Running

Run the game using `run.php` followed by port number and data directory:

    $ php run.php 4000 ../gauntlet-data/

## License

AGPL 3.0
