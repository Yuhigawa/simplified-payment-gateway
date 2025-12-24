const axios = require("axios");

let usersReady = false;
let sharedVars = {};

module.exports = {
  before: async function (context, events) {
    if (usersReady) {
      context.vars = sharedVars;
      return;
    }

    const baseUrl = "http://localhost:9501";

    async function createUser(payload) {
      const res = await axios.post(
        `${baseUrl}/api/v1/accounts/users`,
        payload
      );
      return res.data.id;
    }

    const ts = Date.now();

    const cpfUser1Id = await createUser({
      name: "CPF User 1",
      email: `cpf1_${ts}@loadtest.com`,
      password: "Test123!@#",
      document: Math.floor(Math.random() * 1e11),
      document_type: "cpf",
      balance: 1_000_000_000
    });

    const cpfUser2Id = await createUser({
      name: "CPF User 2",
      email: `cpf2_${ts}@loadtest.com`,
      password: "Test123!@#",
      document: Math.floor(Math.random() * 1e11),
      document_type: "cpf",
      balance: 1_000_000_000
    });

    const cnpjUserId = await createUser({
      name: "Merchant",
      email: `merchant_${ts}@loadtest.com`,
      password: "Test123!@#",
      document: Math.floor(Math.random() * 1e14),
      document_type: "cnpj",
      balance: 0
    });

    sharedVars = {
      cpfUser1Id,
      cpfUser2Id,
      cnpjUserId
    };

    usersReady = true;
    context.vars = sharedVars;
  }
};
