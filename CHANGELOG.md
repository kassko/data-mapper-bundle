`0.14.0.0-alpha - 2015/03/24 - Break version:`
** Break **: The DataMapper component has changed and compatibility is broken. See DataMapper changelog for 0.12.0.0.

`0.13.0.0-alpha - 2015/03/09 - Break version:`
** Break **: Add new keys in data source mapping configuration and modify the behaviour

`0.12.0.0-alpha - 2015/03/09 - Break version:`
** Break **: Rename a key in mapping configuration: provider to data source

`0.11.0.0-alpha - 2015/01/04 - Break version:`
** Break **: Rename some keys in mapping configuration

`0.10.0.0-alpha - 2014/12/16 - BREAK version:`
** Break **: The DataMapper class API, and the ResultBuilder class API have changed after renaming some methods with shorter names.

`0.9.0.0-alpha - 2014/12/16 - BREAK version:`
* Break *: The service kassko_data_mapper.result_builder_factory is removed, use the service kassko_data_mapper instead with the same API. Exception the create() method is replaced by createResultBuilder().

* Break *: The service kassko_data_mapper.query_factory is removed, use DataMapper instead with the same API.

* New * : Add a service kassko_data_mapper which contains the interface of ResultBuilderFactory and QueryFactory
