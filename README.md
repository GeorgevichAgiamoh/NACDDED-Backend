# NACDDED Backend

## Info
Company: Stable Shield Solutions

Developer: Daniel, Remote fullstack developer

Technology: PHP, Laravel, MySQL

Started: 22/1/24



## Progress Update

> Setting up authentication REST API (DONE)

> User data upload endpoint (~)

> Testing (~)

> Provisioning admin (~)

> Building more endpoints (~)

> Sending Mails (~) 

> Optimizing ( ~ )

> Messaging (~)




## Notes on Data Structure

All values are in string (no date, or int). Not relevant to the frontend engineer but short values are `string` while long ones are `text`.

> Date is strored as milliseconds since epoch (again, in string). This includes info like DOB

> Passwords are encrypted. Authentication is through JWT.

> There are no nested JSON.

> Some props have values of 0/1 (this means NO and YES rsp.)

> Endpoints used to create data can also be called to update data

Please follow the data structure strictly or an error will be thrown. For error handling:





### Error Handling

Successful and Failed requests cannot be distinguished by the HTTP error code alone. Internal server errors (such as failed payload validation) will return error codes other than `200`. For instance, unauthorized access to a protected endpoint will return `401`. In any case, the JSON response payload will contain error message. 

For other kinds of error (such as non-existent data), code of `200` will be returned but the `status` prop will be false. To truly know of a request was successful, the following conditions must be met:

- Code of `200` is returned
- status prop is true

If status prop is false, a message will be included in the message prop indicating why it failed. The structure is:

```json
{
    "status": true,
    "message": "Message",
    "pld": "JSON payload (ie the data requested for)",
}
```





### Auth

When you successfully call the `login` endpoint, an access token will be returned such as:

```json
{
    "status": true,
    "message": "User login successfully",
    "token": "A long token string",
}
```

While you can call `UNPROTECTED` endpoints without authentication, you will need to include this token for other endpoints (include it as a `Bearer` token) 





## Data Structure


``UNPROTECTED``

No auth required to access these endpoints


### Register (POST, register)

```json
{
    "email":"required|email|unique:users",
    "password": "required",
}
```



### Login (POST, login)

```json
{
    "email":"required|email",
    "password": "required",
}
```




``PROTECTED``



### Admin Auth (POST, authAsAdmin)

This endpoint requires no payload. To authenticate an admin, you must call this endpoint after you call the `login` endpoint. Once called, we will verify the user is an admin using info from the `token` and add more claims to the token to control his/her access. 

> NOTE: If you try to access data which this admin does not have permission for, a `401` wil be thrown



### Create Admin (POST, setAdmin)

pd1 = Permission to read from Directory
pd2 = Permission to write to directory
... Same login to others.

```json
{
    "email":"required|email",
    "lname": "required",
    "oname": "required",
    "role": "required",
    "pd1": "required",
    "pd2": "required",
    "pw1": "required",
    "pw2": "required",
    "pp1": "required",
    "pp2": "required",
    "pm1": "required",
    "pm2": "required",
}
```