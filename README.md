# Craft Commerce Stock Notifications plugin for Craft CMS 3.x

Add a signup form to out of stock products that automatically emails them when the product is back in stock.

## Requirements

* Craft CMS 3.
* Craft Commerce 2.

## Installation

To install the plugin, follow these instructions.

1. Open your terminal and go to your Craft project:

        cd /path/to/project

2. Then tell Composer to load the plugin:

        composer require mandarindesign/craft-commerce-stock-notifications

3. In the Control Panel, go to Settings → Plugins and click the “Install” button for Craft Commerce Stock Notifications.

## Overview

* Users (and guests) can sign up to be automatically emailed when specific products are back in stock.
* Users can delete their own notifications if they want.

## Usage

### 1. Products without variants

```
{% if not product.defaultVariant.hasStock %}
    {% set param = craft.app.request.param('notification') %}

    {% if param == 'saved' %}
        <p>Notification saved.</p>
    {% elseif param == 'deleted' %}
        <p>Notification deleted.</p>
    {% endif %}

    {% if not craft.craftCommerceStockNotifications.notificationSet(product.id) %}
        <form action="/actions/craft-commerce-stock-notifications/stock/save-notification" method="post" accept-charset="UTF-8">
            {{ csrfInput() }}
            <input type="hidden" name="productId" value="{{ product.id }}">
            <input type="hidden" name="variantId" value="{{ product.defaultVariant.id }}">
            <input type="email" name="email" value="{{ craft.craftCommerceStockNotifications.notificationSet(product.id) }}" placeholder="Your email">
            <input type="submit" class="" value="Notify me">
        </form>
    {% else %}
        <p>
            You will be notified via {{ craft.craftCommerceStockNotifications.notificationSet(product.id) }} when this product is back in stock.
            <a href="/actions/craft-commerce-stock-notifications/stock/delete-notification?productId={{ product.id }}&email={{ craft.craftCommerceStockNotifications.notificationSet(product.id) }}">Delete
                notification</a>
        </p>
    {% endif %}
{% endif %}
```

### 2. Products with variants

```
Coming...
```

It might be a good idea to hide/show the form depending on the selected variant. You
can do that e.g. with jQuery.

```
// Assuming variants are changed with a select list
$.example coming...
```

## Roadmap

* Handle products with variants.
* Remember guest emails with cookies for a better guest experience.

Brought to you by [Mandarin Design](https://mandarindesign.no)
