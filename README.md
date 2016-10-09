.checkout
=========

A Symfony project created on September 4, 2016, 4:08 pm.


API endpoints:
--------------------------------------------------------
1) Create user:
http://vitchkovski.com/api/users/new

Example:
curl -v -H "Accept: application/json" -H "Content-type: application/json" POST -d "{\"registration\": {\"username\":\"foo3\", \"email\": \"foo3@example.org\", \"password\": \"123456\"}}" http://vitchkovski.com/api/users/new
--------------------------------------------------------
2) Get user's API token:
http://vitchkovski.com/api/users/key

Example:
curl -v -H "Accept: application/json" -H "Content-type: application/json" POST -d "{\"user\": {\"email\":\"lsa15@gmail.com\", \"password\": \"123123\"}}" http://vitchkovski.com/api/users/key
--------------------------------------------------------
3) Get info for the logged user using API token:
http://vitchkovski.com/api/users/me

Example:
curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/users/me
--------------------------------------------------------
4) Get public info for the user:
http://vitchkovski.com/api/users/{user_id}

Example:
curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/users/1
--------------------------------------------------------
5) Get public info for all users:
http://vitchkovski.com/api/users

Example:
curl -H "X-AUTH-TOKEN: ee8cc5f8cf56e99c366bcc2361f0031f" http://vitchkovski.com/api/users
--------------------------------------------------------
6) Get product info:
http://vitchkovski.com/api/products/{product_id}

Example:
curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/products/1
--------------------------------------------------------
7) Get all products  for the user:
http://vitchkovski.com/api/products

Example:
curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/products
--------------------------------------------------------
8) Create new product:
http://vitchkovski.com/api/products/new

Example:
curl -v -H "Accept: application/json" -H "Content-type: application/json" -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" POST -d "{\"product\": {\"product_name\": \"foo\"}}" http://vitchkovski.com/api/products/new
--------------------------------------------------------
9) Delete  product:
http://vitchkovski.com/api/products/{product_id}/remove

Example:
curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/products/11/remove
--------------------------------------------------------
10) Edit product:
http://vitchkovski.com/api/products/{product_id}/edit

Example:
curl -v -H "Accept: application/json" -H "Content-type: application/json" -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" PUT -d "{\"product\":{\"product_name\":\"QWERTY3\",\"categories\":[{\"category_name\":\"\"},{\"category_name\":\"Category3\"}]}}" http://vitchkovski.com/api/products/16/edit
-------------------------------------------------------
11) Get category info:
http://vitchkovski.com/api/categories/{category_id}

Example:
curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/categories/7
-------------------------------------------------------
12) Remove category:
http://vitchkovski.com/api/categories/{category_id}/remove

Example:
curl -H "X-AUTH-TOKEN: a846112941c879a6866cf252d5eaf0a7" http://vitchkovski.com/api/categories/7/remove
-------------------------------------------------------