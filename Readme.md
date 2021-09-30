# Description

This plugin is an add-on for the awesome WP GraphQL

it buils on top of  WP GraphQL to add support for mutationing subscriber

## Installing

1. Make sure that WP GraphQL is installed and activated first.
2. Upload this repo (or git clone) to your plugins folder and activate it.

## Usage

#### Mutation

```graphql
mutation subscribe {
  subscribe(input: {esfpx_email: "hieunguyenel+9911822@gmail.com", esfpx_name: "hieu nguyen", esfpx_lists: "3379fa33bf96"}) {
    subscribeId
  }
}
```

