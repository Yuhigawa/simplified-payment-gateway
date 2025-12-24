// processor.js - Artillery processor for dynamic user IDs

let globalUserIds = {
  cpfUser1Id: null,
  cpfUser2Id: null,
  cnpjUserId: null
};

// Store user IDs globally when they're created
function captureUserId(requestParams, response, context, ee, next) {
  if (response.body) {
    try {
      const body = JSON.parse(response.body);
      
      if (body.id) {
        // Store globally based on document type
        if (body.document_type === 'cpf') {
          if (!globalUserIds.cpfUser1Id) {
            globalUserIds.cpfUser1Id = body.id;
            console.log(`CPF User 1 created: ${body.id}`);
          } else if (!globalUserIds.cpfUser2Id) {
            globalUserIds.cpfUser2Id = body.id;
            console.log(`CPF User 2 created: ${body.id}`);
          }
        } else if (body.document_type === 'cnpj') {
          globalUserIds.cnpjUserId = body.id;
          console.log(`CNPJ User created: ${body.id}`);
        }
      }
    } catch (e) {
      // Ignore parse errors
    }
  }
  
  return next();
}

// Inject stored user IDs into context
function setUserIds(context, events, done) {
  // Use global IDs for all virtual users
  context.vars.cpfUser1Id = globalUserIds.cpfUser1Id;
  context.vars.cpfUser2Id = globalUserIds.cpfUser2Id;
  context.vars.cnpjUserId = globalUserIds.cnpjUserId;
  
  return done();
}

// Generate random CPF (11 digits)
function generateCPF(context, events, done) {
  const cpf = Math.floor(Math.random() * 90000000000) + 10000000000;
  context.vars.randomCPF = cpf.toString();
  return done();
}

// Generate random CNPJ (14 digits)
function generateCNPJ(context, events, done) {
  const cnpj = Math.floor(Math.random() * 90000000000000) + 10000000000000;
  context.vars.randomCNPJ = cnpj.toString();
  return done();
}

// Log transaction results
function logTransaction(requestParams, response, context, ee, next) {
  if (response.statusCode === 200 || response.statusCode === 201) {
    try {
      const body = JSON.parse(response.body);
      console.log(`✅ Transaction ${body.id}: ${body.value} from ${body.payer.id} to ${body.payee.id}`);
    } catch (e) {
      // Ignore
    }
  } else if (response.statusCode >= 400) {
    console.log(`❌ Transaction failed: ${response.statusCode}`);
  }
  return next();
}

module.exports = {
  captureUserId,
  setUserIds,
  generateCPF,
  generateCNPJ,
  logTransaction
};