Feature: frontend500Check
  Test each link in frontend, so it shouldn't find 500 server errors

Scenario: Each link in frontend area replies with another http status code than 500
  Given I am in frontend
  When I visit all links I find on each page in frontend
  Then I don't get the http status code "500" in frontend
  Then I close the session