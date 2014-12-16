
0.9.0.0-alpha - 2014/12/16 - BREAK version:
* New * : Add a service kassko_data_mapper which contains the interface of ResultBuilderFactory and QueryFactory
* Break *: The service kassko_data_mapper.result_builder_factory is removed, use the service kassko_data_mapper instead with the same API. Exception the create() method is replaced by createResultBuilder().
* Break *: The service kassko_data_mapper.query_factory is removed, use DataMapper instead with the same API.