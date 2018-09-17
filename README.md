# Garlic GraphQL bundle (under development)

This bundle allow microservices send to each other graphql queries 
For correct usage the Bundle must be installed on both services (current and target)

This bundle based on [youshido-php/GraphQLBundle](https://github.com/youshido-php/GraphQL), so special thanks to this guys for the excellent work! We've just made a couple updates ;)

## Configuration and Usage

There are necessary thing make this bundle works:

### Add bundle to the Symfony project

```bash
composer require garlic/graphql
```

### Initialize GraphQL schema(create schema, query and mutation fields)

```bash
bin/console maker:graphql:init
```

### Create graphql type (command able to get fields from existing Entity)

```bash
bin/console maker:graphql:type
```

The command suggest you to create full CRUD mutations and queries, just type "y" to do so. After that the command will 
create some classes and CRUD service with all the functionality. The one thing you have to this service is working, just add next rows to your service.yaml
```yaml
# Make graphql services public
App\Service\GraphQL\:
    resource: '../src/Service/GraphQL/*'
    public: true
```

### Make your first graphql query or mutation

```bash
bin/console maker:graphql:query
```

Now you can review and update just created files! 

It's time to run your first query!