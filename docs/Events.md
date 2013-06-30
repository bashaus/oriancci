---
layout: default
slug  : events
title : Event
---

# Events

## Oriancci\Model

### afterConstruct

Dispatched when the model has been:

* newly constructed 
* retrieved from the database

### afterInstantiation

Dispatched when the model has been:

* newly constructed

Follows event `afterConstruct`

### afterSelection

Dispatched when the model has been:

* retrieved from the database

Follows event `afterConstruct`

### beforeSave

Dispatched when the `Model->save()` method is called

If an event returns false, the save is cancelled

### afterSave

Dispatched when the `Model->save()` method is complete

### beforeInsert

Dispatched when the `Model->insert()` method is called

If an event returns false, the insert is cancelled

Follows event `beforeSave`

### afterInsert

Dispatched when the `Model->insert()` method is complete.

Preceeds event `afterSave`

### beforeUpdate

Dispatched when the `Model->update()` method is called.

If an event returns false, the update is cancelled.

Follows event `beforeSave`

### afterUpdate

Dispatched when the `Model->update()` method is complete.

Preceeds event `afterSave`

### beforeDelete

Dispatched when the `Model->delete()` method is called. 

If an event returns false, the delete is cancelled.

### afterDelete

Dispatched when the `Model->delete()` method is complete.

## Oriancci\Model\BTree

### beforeInsertRoot

Dispatched when the `BTree->insertRoot()` method is called. 

If an event returns false, the delete is cancelled.

### afterInsertRoot

Dispatched when the `BTree->insertRoot()` method is completed. 

### beforeInsertBefore

Dispatched when the `BTree->insertBefore()` method is called. 

If an event returns false, the delete is cancelled.

### afterInsertBefore

Dispatched when the `BTree->insertBefore()` method is completed. 

### beforeInsertAfter

Dispatched when the `BTree->insertAfter()` method is called. 

If an event returns false, the delete is cancelled.

### afterInsertAfter

Dispatched when the `BTree->insertAfter()` method is completed. 

### beforeAppendTo

Dispatched when the `BTree->appendTo()` method is called. 

If an event returns false, the delete is cancelled.

### afterAppendTo

Dispatched when the `BTree->appendTo()` method is completed. 