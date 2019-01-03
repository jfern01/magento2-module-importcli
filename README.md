# magento2-module-importcli
A console command for importing catalog files. Based on [cedricblondeau/magento2-module-catalog-import-command.](https://github.com/cedricblondeau/magento2-module-catalog-import-command)

##Usage

```
bin/magento catalog:product:import --help

Description:
  Import catalog

Usage:
  catalog:product:import [options] [--] <filename>

Arguments:
  filename                                             CSV file path

Options:
  -b, --behavior[=BEHAVIOR]                            Behavior [default: "append"]
      --field_separator[=FIELD_SEPARATOR]              Field separator (delimiter) [default: ","]
      --multi_value_separator[=MULTI_VALUE_SEPARATOR]  Muliple field separator [default: ","]
  -f, --fields_enclosure                               Fields Enclosure
      --validate                                       Validate data only (no import)
  -i, --images_path[=IMAGES_PATH]                      Images path [default: "pub/media/catalog/product"]
  -h, --help                                           Display this help message
  -q, --quiet                                          Do not output any message
  -V, --version                                        Display this application version
      --ansi                                           Force ANSI output
      --no-ansi                                        Disable ANSI output
  -n, --no-interaction                                 Do not ask any interactive question
  -v|vv|vvv, --verbose                                 Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug

```