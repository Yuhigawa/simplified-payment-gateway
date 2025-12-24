const axios = require("axios");

let usersReady = false;
let sharedVars = {};

module.exports = {
  // before: async function (context, events, done) {
  //   if (usersReady) {
  //     console.log('UsersReady: ', usersReady)
  //     console.log('SharedVars: ', sharedVars)
  //     context.vars = sharedVars;
  //     return done();
  //   }

  //   const baseUrl = "http://localhost:9501";
  //   const ts = Date.now();

  //   async function createUser(payload) {
  //     const res = await axios.post(`${baseUrl}/api/v1/accounts/users`, payload);
  //     return res.data.id;
  //   }

  //   const cpfUser1Id = await createUser({
  //     name: "CPF User 1",
  //     email: `cpf1_${ts}@loadtest.com`,
  //     password: "Test123!@#",
  //     document: Math.floor(Math.random() * 1e11),
  //     document_type: "cpf",
  //     balance: 1_000_000_000
  //   });

  //   const cpfUser2Id = await createUser({
  //     name: "CPF User 2",
  //     email: `cpf2_${ts}@loadtest.com`,
  //     password: "Test123!@#",
  //     document: Math.floor(Math.random() * 1e11),
  //     document_type: "cpf",
  //     balance: 1_000_000_000
  //   });

  //   const cnpjUserId = await createUser({
  //     name: "Merchant",
  //     email: `merchant_${ts}@loadtest.com`,
  //     password: "Test123!@#",
  //     document: Math.floor(Math.random() * 1e14),
  //     document_type: "cnpj",
  //     balance: 0
  //   });

  //   console.log('Users: ', { cpfUser1Id, cpfUser2Id, cnpjUserId });

  //   sharedVars = { cpfUser1Id, cpfUser2Id, cnpjUserId };
  //   context.vars = sharedVars;
  //   usersReady = true;

  //   console.log("[Artillery] Users ready:", sharedVars);
  //   return done();
  // },

  // buildTransfer: function (context, events, done) {
  //   if (!context.vars.cpfUser1Id || !context.vars.cnpjUserId) {
  //     console.error("Users not ready yet!");
  //     return done(new Error("Users not ready"));
  //   }

  //   const payer = Math.random() < 0.5 ? context.vars.cpfUser1Id : context.vars.cpfUser2Id;
  //   context.vars.$json = {
  //     value: 1,
  //     payer,
  //     payee: context.vars.cnpjUserId
  //   };

  //   console.log(context.vars.$json)

  //   return done();
  // },

  buildTransfer: function (context, events, done) {
    cpfUser1Id = "862458001043587073"
    cpfUser2Id = "862458025056985089"
    cnpjUserId = "862458096462782465"

    const payer = Math.random() < 0.5 ? cpfUser1Id : cpfUser2Id;

    const possiblePayees = [cpfUser1Id, cpfUser2Id, cnpjUserId];
    const filteredPayees = possiblePayees.filter(id => id !== payer);
    const payee = filteredPayees[Math.floor(Math.random() * filteredPayees.length)];

    context.vars.$json = {
      value: 1,
      payer,
      payee,
    };

    return done();
  },

  logRequest: function (requestParams, context, ee, next) {
    console.log("[Artillery] Request payload:", requestParams.json);
    return next();
  },

  logAfter: function (requestParams, response, context, ee, next) {
    console.log("[Artillery] Sent payload:", requestParams.json);
    return next();
  },
};
