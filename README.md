Basic NDC Flow( Booking and Cancellation ): 
NDC APIs: AirShoppingRQ, OfferPriceRQ, OrderCreateRQ, OrderRetrieveRQ, OrderReshopRQ( Reprice ), OrderChangeRQ, OrderCancelRQ
Workflow: AirShoppingRQ > OfferPriceRQ > OrderCreateRQ > OrderRetrieveRQ > OrderReshopRQ( Reprice ) > OrderChangeRQ > OrderCancelRQ
TC1: Any Route, Direct/One-way, 1ADT
Order is Booked
Order is Repriced
Order is Ticketed
Order is Cancelled


order reshop, == not req for logs

OrderReshopRQ (use for update flight like new flight you have to submit order ids of new flight with this)


Workflow:
    ..AirShoppingRQ > ..OfferPriceRQ > ..OrderCreateRQ > ..OrderRetrieveRQ > OrderReshopRQ( Reprice ) > ...OrderChangeRQ > ...OrderCancelRQ


Authentication URL - https://aero-suite-stage4-airarabia.isaaviation.net/api/auth/authenticate
login - ABYDEST_ONEAPI
password - P@ss1234
From Price to Book use the WSDL file:
WSDL: https://g94.isaaviations.com/webservices/services/AAResWebServices?wsdl
URL for API: https://g94.isaaviations.com/webservices/services/AAResWebServices
USER ID: DEST_ONEAPI
Password: P@ss1234
Agent Code: AACKHI2717