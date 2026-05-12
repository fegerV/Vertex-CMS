# Conditional Logic Guide

Dynamic field visibility based on user input.

## Operators

- equals – exact match
- 
ot_equals – not equal
- contains – substring contains
- greater_than – numeric greater than
- less_than – numeric less than
- is_empty – field empty
- is_not_empty – field has value

## Example

`json
{
  "name": "company_name",
  "type": "text",
  "conditional": {
    "depends_on": "user_type",
    "operator": "equals",
    "value": "business"
  }
}
`

Shows company_name only when user_type equals "business".

## Backend

Server re-evaluates conditions via FormConditionEngine::evaluateFields().
