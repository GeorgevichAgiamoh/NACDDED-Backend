# NACDDED Backend


## Info
Company: Stable Shield Solutions

Developer: Daniel, Remote fullstack developer

Technology: PHP, Laravel, MySQL

Started: 22/1/24



## Progress Update

> Setting up authentication REST API (DONE)

> User data upload endpoint (DONE)

> Testing (DONE)

> Provisioning admin (DONE)

> Sending Mails (DONE) 

> Building more endpoints (~)

> Optimizing (~)

> Messaging (~)





## Notes on Data Structure

All values are in string (no date, or int). Not relevant to the frontend engineer but short values are `string` while long ones are `text`.

- Date is strored as milliseconds since epoch (again, in string). This includes info like DOB

- Passwords are encrypted. Authentication is through JWT.

- There are no nested JSON.

- Some props have values of 0/1 (this means NO and YES rsp.)

- Endpoints used to create data can also be called to update data

- (,,) means implied

- For ids (like memid and adminId) please pass email instead. Though its still in debate if an ID system will be in place

- QP means query param

- All data include the `created_at` and `updated_at` prop (UTC date). For instance, you can use it to get the date of initial registration 
 
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


### Notes about `GET` requests

Remember, the response payload is as described in the JSON above. The actual data is in `pld` 



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



## Files

All files are stored in the public/upload dir


### Upload File (`POST`, uploadFile)

```json
{
    "file" : "required", //|mimes:jpeg,png,jpg,gif,svg|max:2048
    "filename" : "required",
    "folder" : "required",
}
```


### Get file (`GET`, getFile)

This should just be a link you pass to something like an `a` tag. Construct the link as:

{root}/{foldername}/{filename}

`foldername` & `filename` are those you provided when uploading the file.


### Check whether file exists (`GET`, fileExists)

Same logic as abov, except this endpoint returns a json so you should call it as a REST API. A successful response will be:

```json
{
    "status" : true,
    "message" : "Yes, it does",
}
```




## Emails

> Only admin accounts can send emails

You can send emails using:

### Send Email endpoint (`POST`, sendMail)

The actual email template has been designed with php blade. But you can control what it says with the payload structure:

```json
{
    "name":"required",
    "email":"required",
    "subject":"required",
    "body": "required",
}
```


## Payments

Every payment has a `ref`. You must provide this reference either to this server when creating manual payment record, or you provide it to paystack (paystack will pass it to this server after user makes payment). The `ref` has a strict structure:

nacdded-(payId)-(amt)-(email)-(mills)

- `payId` is the type of payment. 0 for annual dues, 1 for events, 2 for donations

- `mills` is milliseconds since epoch. It can be anything as long as its unique. Also, you may use it as that particular payment record id 




## Data Structure

> **NOTE** While this data structure is still relevant, i stopped updating it late Jan 2024 - New docs are on the swagger UI 


``UNPROTECTED``

No auth required to access these endpoints


### Register (`POST`, register)

```json
{
    "email":"required|email|unique:users",
    "password": "required",
}
```



### Login (`POST`, login)

> Call the adminlogin endpoint for admins

```json
{
    "email":"required|email",
    "password": "required",
}
```




``PROTECTED``



### Admin Auth (`POST`, authAsAdmin)

This endpoint requires no payload. To authenticate an admin, you must call this endpoint after you call the `login` endpoint. Once called, we will verify the user is an admin using info from the `token` and add more claims to the token to control his/her access. 

> NOTE: If you try to access data which this admin does not have permission for, a `401` wil be thrown



### Create Admin (`POST`, setAdmin)

``ADMIN``

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

Of course a first admin needs to exists. To create it just ``POST`` to `setFirstAdminUserInfo` endpoint. No need to pass any param. Ps. its an unprotected endpoint so no need to token. You can call it from postman.


### Get Admin(s) (`GET`, getAdmin(s))

Use `getAdmin` for single admin or `getAdmins` for all admins. If using `getAdmin` include the adminId `path param`

{root}/getAdmin/{adminId} OR {root}/getAdmins



### Remove an admin (`GET`, removeAdmin)

``ADMIN``

`GET` the endpoint:

{root}/removeAdmin/{adminID}


### Create Announcement (`POST`, setAnnouncements)

``ADMIN``

```json
{
    "title":"required",
    "msg": "required",
    "time": "required",
}
```

### Get Announcement (`GET`, getAnnouncements)

> See `POST` method



### Get Highlights (`GET`, getHighlights)

The info tabs on admin dashboard (first page).

> Still under dev

```json
{
    "status": "true",
    "message": "Success",
    "pld": {
        "totalUsers":",,",
        "totalSchools":",,",
        "totalDiocese": ",,"
    },
}
```



### Create Event (`POST`, setEvents)

``ADMIN``

```json
{
    "title":"required",
    "time": "required",
    "venue": "required",
    "fee": "required",
    "start": "required",
    "end": "required",
    "theme": "required",
    "speakers": "required - recommended comma separated",
}
```


### Get Events (`GET`, getEvents)

If you want to get all events, call this endpoint without any payload. Alternatively, you can limit the result returned by passing a `count` query parameter.

> The `count` is a ``query`` parameter and must be a positive integer


### setDioceseBasicInfo (`POST`, setDioceseBasicInfo)

> The `verif` prop has value 0/1 and it tells if that user has been verified.

```json
{
    "diocese_id":"required",
    "name": "required",
    "phn": "required",
    "verif":"required",
}
```

> The `email` must exist as a registered user.


### getDioceseBasicInfo (`GET`, getDioceseBasicInfo/{dioceseId})



### setDioceseGeneralInfo (`POST`, setDioceseGeneralInfo)

```json
{
    "diocese_id":"required",
    "state": "required",
    "lga": "required",
    "addr": "required",
}
```

### getDioceseGeneralInfo (`GET`, getDioceseGeneralInfo/{dioceseId})




### setSecretaryInfo (`POST`, setSecretaryInfo)

```json
{
    "email":"required",
    "fname": "required",
    "lname": "required",
    "mname": "required",
    "sex": "required",
    "phn": "required",
    "addr": "required",
    "diocese_id": "required",
}
```

### getSecretaryInfo (`GET`, getSecretaryInfo)

Requires `email` QP


### getDioceseSecretaries (`GET`, getDioceseSecretaries/{dioceseId})



### Upload Payment Record (`POST`, uploadPayment)

``ADMIN``

For manually uploading records. 

```json
{
    "ref": "required",
    "name": "required",
    "time": "required",
    "year": "optional - for annual dues only",
    "event": "optional - for events only - pass EVENT-ID",
}
```


### Get Payment records (`GET`, getPayments)

Requires `payId` path parameter: getPayments/{payId}

Limit the no. of records retrieved with the `start` and `count` query params (integer). `start` specifies start index and `count` limits the result. If you dont provide it, the endpoint will return only the first 20 records 




### getDiocesePayments (GET, getDiocesePayments/{payId})

> Requires the `email` QP and payId path param (payId is 0(dues) 1(events))

> Can accept `count` and `start` QP

Get all the payments by that Diocese. 



### setNacddedInfo (`POST`, setNacddedInfo)

``ADMIN``

```json
{
    "email":"required",
    "cname":"required",
    "regno": "required",
    "addr": "required",
    "nationality":"required",
    "state": "required",
    "lga": "required",
    "aname":"required",
    "anum": "required",
    "bnk": "required",
    "pname":"required",
    "peml": "required",
    "pphn": "required",
    "paddr":"required",
}
```


### getNacddedInfo (`GET`, getNacddedInfo)

``ADMIN``

> Requires the `email` QP



### Create Diocese School (`POST`, setMySchool)

> Get `diocese_id` from `getMyDiocese` endpoint

```json
{
    "diocese_id":"required",
    "name": "required",
    "type": "required",
    "lea":"required",
    "addr": "required",
    "email": "required|email",
    "phone":"required",
    "p_name": "required",
    "p_email": "required",
    "p_phone":"required",
}
```


### Get Diocese Schools (`GET`, getMySchools/{dioceseId})

> Can accept `count` and `start` QP



### Get All Schools (`GET`, getSchools)

> Can accept `count` and `start` QP

