# Drag & Drop su Konfliktų Override Funkcionalumas

## Aprašymas

Pridėta galimybė mokyklos administratoriui perkelti grupes (pamokas) tvarkaraštyje naudojant **drag and drop** principą, net jei yra konfliktai. Sistema parodo įspėjimą apie konfliktus ir leidžia administratoriui **vis tiek perkelti** pamoką po patvirtinimo.

## Kaip veikia

### 1. **Drag and Drop be konfliktų**
- Administratorius gali tempti:
  - **Nesuplanuotas grupes** iš šoninio skydelio ant mokytojo tvarkaraščio langelio
  - **Jau suplanuotas pamokas** iš vieno langelio į kitą (tame pačiame mokytojo eilutėje)
- Jei nėra konfliktų, pamoka iš karto pridedama/perkeliama po patvirtinimo

### 2. **Drag and Drop su konfliktais**
- Kai bandoma perkelti pamoką į laiką, kur yra konfliktas:
  - **Mokytojas** tuo metu jau turi pamoką
  - **Kabinetas** (room) užimtas
  - **Mokiniai** turi kitą pamoką tuo metu
  - Viršytas **vieno dalyko pamokų skaičius** per dieną

- Sistema automatiškai aptinka konfliktus ir parodo **įspėjimo modal'ą**:
  ```
  ┌─────────────────────────────────────┐
  │ ⚠️ Aptikti konfliktai               │
  ├─────────────────────────────────────┤
  │ Grupė: 1A Matematika                │
  │ Laikas: Pirmadienis, 3 pamoka       │
  │                                     │
  │ ⚠️ Aptikti šie konfliktai:         │
  │ • Mokytojas užimtas tuo laiku      │
  │ • Kabinetas 101 užimtas            │
  │ • Užimti mokiniai:                 │
  │   - Jonas Jonaitis (2A grupė)      │
  │   - Petras Petraitis (2B grupė)    │
  │                                     │
  │ ℹ️ Jei pridėsite šią pamoką, bus   │
  │   sukurtas tvarkaraščio konfliktas  │
  ├─────────────────────────────────────┤
  │ [Atšaukti]  [⚠️ Vis tiek pridėti]  │
  └─────────────────────────────────────┘
  ```

### 3. **Patvirtinimas "Vis tiek pridėti"**
- Administratorius mato visus konfliktus
- Gali nuspręsti ar vis tiek perkelti pamoką
- Paspaudus **"Vis tiek pridėti"** arba **"Vis tiek perkelti"** - pamoka pridedama nepaisant konfliktų
- Sistema pažymėja sėkmę geltonu pranešimu: *"Pamoka perkelta (su konfliktais)"*

### 4. **Swap (Sukeitimas)**
- Jei tempiate pamoką į langelį, kur jau yra kita pamoka **to paties mokytojo**, sistema pasiūlo **sukeisti** pamokas vietomis
- Parodo swap konfirmaciją:
  ```
  ┌─────────────────────────────────────┐
  │ ↔️ Sukeisti pamokų vietomis?        │
  ├─────────────────────────────────────┤
  │ Keliama pamoka:                     │
  │ 1A Matematika (Matematika)          │
  │                                     │
  │ Esanti pamoka:                      │
  │ 2B Fizika (Fizika)                  │
  │                                     │
  │ ℹ️ Ar norite sukeisti šias dvi      │
  │   pamokas vietomis?                 │
  ├─────────────────────────────────────┤
  │ [Atšaukti]  [↔️ Sukeisti]          │
  └─────────────────────────────────────┘
  ```

## Techninė implementacija

### Frontend (JavaScript)

#### 1. **Modal dialogai**

**`showConfirmDialog(groupName, subjectName, day, slot, conflictData, groupId)`**
- Rodo konfirmacijos langą prieš pridedant nesuplanuotą grupę
- Jei `conflictData.hasConflicts === true`, rodo konfliktų sąrašą ir mygtuką "Vis tiek pridėti"
- Grąžina `'force'` jei vartotojas patvirtino su konfliktais, `true` jei be konfliktų, `false` jei atšaukė

**`showMoveConfirmDialog(groupName, subjectName, day, slot, conflictData)`**
- Rodo konfirmacijos langą prieš perkeliant jau suplanuotą pamoką
- Panašus į `showConfirmDialog`, bet skirtas perkėlimams
- Grąžina `'force'` arba `false`

**`showSwapDialog(movingGroup, movingSubject, targetGroup, targetSubject, day, slot)`**
- Rodo sukeitimo konfirmaciją
- Grąžina `true` jei vartotojas sutinka sukeisti, `false` jei ne

#### 2. **Drag & Drop handler'iai**

**Pridedant nesuplanuotą grupę (`draggedKind === 'unscheduled'`):**
```javascript
// 1. Tikrina konfliktus su checkConflicts()
const conflicts = await checkConflicts(groupId, teacherId, day, slot);

// 2. Rodo konfirmaciją
const confirmation = await showConfirmDialog(groupName, subjectName, day, slot, conflicts, groupId);

// 3. Nustato force flag
const forceAdd = confirmation === 'force';

// 4. Siunčia užklausą su force parametru
fetch(..., { body: JSON.stringify({ ..., force: forceAdd }) })
```

**Perkeliant suplanuotą pamoką (`draggedKind === 'scheduled'`):**
```javascript
// 1. Bando perkelti
const resp = await fetch(..., { body: JSON.stringify({ ..., swap: false }) });

// 2. Jei klaida susijusi su konfliktu
if (data.error && data.error.includes('užimtas')) {
    // Rodo konfirmaciją su force opcija
    const confirmation = await showMoveConfirmDialog(...);
    
    if (confirmation === 'force') {
        // Kartoja užklausą su force: true
        fetch(..., { body: JSON.stringify({ ..., force: true }) });
    }
}

// 3. Jei reikia swap
if (data.needsSwap) {
    const confirmed = await showSwapDialog(...);
    if (confirmed) {
        fetch(..., { body: JSON.stringify({ ..., swap: true }) });
    }
}
```

### Backend (PHP - TimetableController)

#### 1. **`storeManualSlot()` metodas**

```php
$validated = $request->validate([
    // ...
    'force' => 'nullable|boolean',
]);

$force = $validated['force'] ?? false;

// Konfliktų tikrinimai praleisti jei force=true
if (!$force) {
    // Tikrina mokytoją, kabinetą, mokinius, subject limit
    // Grąžina error 422 jei konfliktas
}

// Įrašo slot nepaisant konfliktų jei force=true
```

#### 2. **`moveSlot()` metodas**

```php
$validated = $request->validate([
    // ...
    'swap' => 'nullable|boolean',
    'force' => 'nullable|boolean',
]);

$force = $validated['force'] ?? false;
$allowSwap = $validated['swap'] ?? false;

// Konfliktų tikrinimai praleisti jei force=true
if (!$force) {
    // Tikrina konfliktus
    // Jei yra konfliktas ir position užimta → siūlo swap
    // Jei yra konfliktas ir position laisva → error
}

// Perkelia/sukei čia nepaisant konfliktų jei force=true arba swap=true
```

## Konfliktų tipai

Sistema tikrina šiuos konfliktų tipus:

1. **Mokytojas užimtas** - mokytojas turi kitą pamoką tuo pačiu laiku
2. **Kabinetas užimtas** - kabinetas priskirtas kitai pamokai tuo metu
3. **Užimti mokiniai** - vienas ar daugiau mokinių turi kitą pamoką
4. **Viršytas dalyko limitas** - per daug tos pačios disciplinos pamokų per dieną (pvz., max 2)

## UI elgsena

### Spalvos ir ikonos:
- **Žalia** kortelė: Nėra konfliktų ✅
- **Geltona** kortelė: Yra konfliktai, bet galima perkelti ⚠️
- **Raudona** kortelė: Kritinė klaida (pvz., grupė nerasta) ❌

### Pranešimai:
- **Sėkmė** (žalias): "Pamoka sėkmingai įtraukta"
- **Sėkmė su konfliktais** (geltonas): "Pamoka perkelta (su konfliktais)"
- **Swap sėkmė** (žalias): "Pamokos sėkmingai sukeistos"

## Naudojimo scenarijai

### Scenarijus 1: Normalus perkėlimas
1. Administratorius tempia grupę "1A Matematika"
2. Padeda ant Pirmadienio 3 pamokos
3. Sistema patikrina - konfliktų nėra
4. Rodo žalią konfirmaciją "Konfliktų nerasta"
5. Spaudžia "Pridėti" → pamoka pridedama

### Scenarijus 2: Perkėlimas su mokinių konfliktu
1. Administratorius tempia grupę "2B Fizika"
2. Padeda ant Antradienio 5 pamokos
3. Sistema aptinka: 3 mokiniai turi kitą pamoką tuo metu
4. Rodo geltoną įspėjimą su mokinių sąrašu
5. Administratorius mato konfliktą ir sprendžia:
   - **Atšaukti** - grįžta atgal
   - **Vis tiek pridėti** - pamoka pridedama su konfliktu
6. Jei pasirinko "Vis tiek pridėti" → pamoka pridedama, rodo geltoną pranešimą

### Scenarijus 3: Swap (sukeitimas)
1. Administratorius tempia "1A Anglų" iš Pirmadienio 2 į Trečiadienį 4
2. Trečiadienio 4 pamokoje jau yra "1A Istorija" (to paties mokytojo)
3. Sistema pasiūlo sukeisti
4. Rodo swap dialogą su abiejų pamokų informacija
5. Administratorius patvirtina
6. Pamokos sukeičiamos vietomis

## Saugumas ir validacija

- ✅ Tik autentifikuoti vartotojai gali tempti
- ✅ Tik mokyklos administratoriai gali perkelti pamokas
- ✅ Grupė gali būti tempiama tik ant **savo** mokytojo eilutės
- ✅ `force` parametras validuojamas kaip boolean
- ✅ Visi konfliktai užregistruojami (net jei praleisti su force)

## Ateities patobulinimai (Rekomendacijos)

1. **Audit log** - fiksuoti visus override veiksmus duomenų bazėje
2. **Konfliktų highlight** - paryškinti konfliktines pamokas tvarkaraštyje
3. **Undo funkcija** - galimybė atšaukti paskutinį perkėlimą
4. **Bulk move** - perkelti kelias pamokas iš karto
5. **Konfliktų statistika** - rodyti bendrą konfliktų skaičių tvarkaraštyje
