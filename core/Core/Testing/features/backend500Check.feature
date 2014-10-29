Feature: backend500Check
  Test each link in backend, so it shouldn't find 500 server errors

Scenario: Each link in backend area replies with another http status code than 500
  Given I am logged in to backend with login "noreply@contrexx.com" and password "123456"
  When I visit all links I find on each page
  Then I don't get the http status code "500"
  Then I close the session