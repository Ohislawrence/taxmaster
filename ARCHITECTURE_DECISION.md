# TaxMaster.ng - Architecture Decision Document

**Date**: February 2026  
**Decision**: Confirm payment model and scope for implementation  
**Status**: PENDING USER CONFIRMATION

---

## Critical Architecture Decision

### Previous Design ❌ (Government Remittance Architecture)

```
User Funds → App Paystack Account → App Escrow → 
Weekly to FIRS via CBN/Remita → Government Account
```

**Cost**: ₦11.5M - 18M  
**Timeline**: 3-4 months  
**Complexity**: Very High  
**Components**: Escrow system, CBN integration, FIRS API, regulatory approval  

**File**: `GOVERNMENT_REMITTANCE_ARCHITECTURE.md` (exists, but don't implement yet)

---

### Your Vision ✅ (Recommended Model)

```
User Syncs Bank (Mono) → App Categorizes → App Calculates → 
User Pays FIRS Directly (Remita/CSF) → User Uploads Receipt → 
App Verifies
```

**Cost**: ~₦3M - 5M  
**Timeline**: 10 weeks (Phase 1)  
**Complexity**: Medium  
**Components**: Mono API, transaction categorization, form generation, receipt verification  

**File**: `TAXMASTER_NG_ROADMAP.md` (new - follows your model)

---

## The Key Difference

| Aspect | Government Remittance | Your Model (Recommended) |
|--------|----------------------|--------------------------|
| **Who pays tax?** | App collects → remits | User pays FIRS directly |
| **App's role** | Tax collector | Compliance advisor |
| **Escrow needed?** | Yes ✅ | No ❌ |
| **FIRS account?** | App needs account | User's account only |
| **User trust?** | Medium (holding money) | High (no money holding) |
| **Regulatory burden** | Highest (taxable agent) | Lower (advisor) |
| **Implementation** | 3-4 months | 10 weeks (Phase 1) |
| **Cost** | ₦11.5M-18M | ₦3M-5M |
| **Risk** | High (regulatory, fund control) | Lower (just info/forms) |

---

## What This Means

### ✅ Keep These Concepts (from Government Remittance Architecture)

- FIRS Form 002 generation
- VAT calculation logic
- Tax computation scheduling
- Compliance deadline tracking
- Email alert system
- Receipt storage
- Tax payment history tracking

### ❌ Discard These (from Government Remittance Architecture)

- Escrow account system
- CBN integration
- Automatic fund transfers
- FIRS government accounts
- Remittance job scheduler
- Compliance officer dashboard
- Fund reconciliation system
- Bank statement reconciliation (overkill)

### ✨ Add These Instead (Your Model)

- **Mono API Integration** - Auto-import transactions
- **AI Transaction Categorization** - Mark VAT sales, expenses, etc
- **Receipt Verification** - User uploads payment proof
- **Remita Payment Helper** - Guide user to FIRS payment
- **Payment Status Tracking** - Monitor who's paid what

---

## Implementation Decision Matrix

### Phase 1 MVP (Recommended Start)

```
MUST HAVE                    | NICE TO HAVE           | DON'T BUILD YET
────────────────────────────────────────────────────────────────────
✅ Mono integration          | Email alerts (P1)      | ❌ Escrow
✅ Categorization AI         | SMS alerts (P2)        | ❌ CBN API
✅ VAT Form 002              | P&L generation (P2)    | ❌ Auto-remittance
✅ Compliance calendar       | Balance sheet (P2)     | ❌ Government accounts
✅ Email alerts              | CAC forms (P2)         | ❌ Fund transfers
✅ Receipt upload/verify     | Mobile app (P4)        | ❌ Audit settlement
✅ AI assistant              | API access (P3)        | ❌ Regulatory reporting
```

### Timeline Impact

**If you build Government Remittance model first**:
- Delay Phase 1 by 8 weeks
- Can't launch MVP until month 4
- Regulatory uncertainty
- Fund custody liability

**If you build Your model first**:
- Launch MVP in 10 weeks
- User-controlled payments (lower risk)
- Regulatory approval simpler
- Focus on UX/features first

---

## Recommendation

**PROCEED WITH YOUR MODEL** because:

1. **Faster to market** - 10 weeks vs 4 months
2. **Lower risk** - No money handling = fewer regulations
3. **User trust** - They control their funds completely
4. **MVP-friendly** - Can launch, iterate, then expand
5. **Future-proof** - Can still add auto-remittance later (Phase 3) once established

**Path Forward**:
- Implement Phase 1 as described in TAXMASTER_NG_ROADMAP.md
- KEEP Government Remittance Architecture document as reference
- After Phase 1 success + 1000+ active users, THEN consider auto-remittance

---

## What's Already Built (Don't Rebuild)

✅ **Ready to Use**:
- Business registration flow
- User authentication
- Subscription system (4 plans)
- Basic AI chat (Deepseek)
- Dashboard UI
- Laravel 11 foundation
- Database seeds
- Frontend build pipeline

❌ **Needs Replacement/Update**:
- Paystack integration (currently collects tax) → repurpose for subscriptions only
- Tax calculation service → enhance for VAT/PAYE
- Dashboard → add compliance calendar
- AI chat → add context from synced transactions

---

## Action Items

### For You (User)

1. **Confirm**: Are you okay with the "user pays FIRS directly" model?
   - [ ] Yes, proceed with TAXMASTER_NG_ROADMAP.md
   - [ ] No, build Government Remittance model instead
   - [ ] Not sure, need to discuss

2. **Decide**: What's your MVP launch target?
   - [ ] Q2 2026 (10 weeks) - Your model
   - [ ] Q3 2026 (start flexible) - Either model
   - [ ] Later (flexible timeline) - Either model

3. **Clarify**: Primary target for MVP?
   - [ ] Freelancers (₦10K tier)
   - [ ] Small SMEs (₦30K tier)
   - [ ] All of above
   - [ ] Accounting firms only

### For Dev Team (After Confirmation)

1. **Setup Phase 1 Sprint**:
   - Week 1-2: Mono API integration
   - Week 3-4: Transaction categorization
   - Week 5-6: Compliance calendar
   - Week 7-8: VAT Form 002 generation
   - Week 9-10: Polish + testing

2. **Lock Dependencies**:
   - Mono API (production account)
   - Email service (SendGrid/AWS SES)
   - PDF library (Snappy)
   - Storage (S3 bucket)

3. **Update Product Requirements**:
   - Remove payments from core (only subscription billing)
   - Shift payment UX to "compliance guidance"
   - Focus on accuracy over automation

---

## Success Criteria (Phase 1 MVP)

When complete, you should be able to:

1. ✅ Freelancer signs up, connects bank, gets synced transactions, sees categorized income/expenses
2. ✅ VAT calculation is automatic, Form 002 is generated pre-filled
3. ✅ Compliance calendar shows next 3 deadlines clearly
4. ✅ AI chat can answer "How much VAT do I owe?" with instant calculation
5. ✅ User can upload payment receipt, app verifies date matches calculation
6. ✅ Email reminder arrives 14 days before VAT deadline

**If all 6 work**: You can launch, acquire users, and iterate on Phase 2.

---

## What to Rename/Update

### Files to Archive (Don't Delete)
- `GOVERNMENT_REMITTANCE_ARCHITECTURE.md` → Move to `/reference` folder or mark as "FUTURE"

### Files to Create
- `TAXMASTER_NG_ROADMAP.md` ← NEW (describes your model)
- `PHASE_1_IMPLEMENTATION_GUIDE.md` ← For dev team (coming next)

### Files to Update
- Update `QUICK_START.md` with Mono setup instructions
- Update `README.md` with new vision statement
- Update `config/taxmaster.php` to remove remittance settings

---

## Next Steps

1. **Confirm your model choice** (user pays FIRS directly?)
2. **I'll create Phase 1 detailed spec** with code examples
3. **We'll plan sprint breakdown** (tasks, assignments, timeline)
4. **You'll decide hiring/resource** allocation

**What do you want to do next?**

- [ ] Proceed with Phase 1 as described
- [ ] Discuss Government Remittance again
- [ ] Modify the roadmap (different priorities/timeline)
- [ ] Something else
