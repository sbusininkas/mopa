# Sidebar Struktūros Pertvarkymаs

## Aprašymas

Pertvarkyta sidebar (šoninio meniu) struktūra į modulinę sistemą, kuri rodo skirtingus meniu elementus priklausomai nuo vartotojo rolės.

## Sukurti failai

### 1. **partials/sidebar.blade.php** (Pagrindinis kontroleris)
Pagrindinis sidebar failas, kuris nustato, kurį sidebar komponentą rodyti:
- Tikrina vartotojo rolę (`isSupervisor()`, `isSchoolAdmin()`, `isTeacher()`, `isStudent()`)
- Įkelia atitinkamą sidebar komponentą
- Rodo įspėjimą, jei vartotojas neturi priskirtos rolės

### 2. **partials/sidebar-supervisor.blade.php**
Sistemos administratoriaus (Supervisor) meniu:
- **Mokyklos** - mokyklų valdymas
- **Vartotojai** - vartotojų valdymas
- Active link highlighting

### 3. **partials/sidebar-school-admin.blade.php**
Mokyklos administratoriaus meniu:
- **Dashboard** - mokyklos apžvalga
- **Klasės** - klasių valdymas
- **Importavimas** - duomenų importavimas
- **Prisijungimo raktai** - mokinių/mokytojų raktai
- **Dalykai** - dalykų valdymas
- **Tvarkaraščiai** - tvarkaraščių kūrimas ir valdymas
- **Kabinetai** - kabinetų valdymas

**Nustatymai sekcija:**
- **Mokyklos duomenys** - bendri mokyklos duomenys
- **Kontaktai** - mokyklos kontaktinė informacija

### 4. **partials/sidebar-teacher.blade.php**
Mokytojo meniu (PLACEHOLDER - funkcijos dar nekurtos):
- Mano tvarkaraštis (disabled)
- Mano grupės (disabled)
- Mano dalykai (disabled)
- Info pranešimas: "Mokytojo funkcijos bus pridėtos greitai"

### 5. **partials/sidebar-student.blade.php**
Mokinio meniu (PLACEHOLDER - funkcijos dar nekurtos):
- Mano tvarkaraštis (disabled)
- Mano pamokos (disabled)
- Pažymiai (disabled)
- Info pranešimas: "Mokinio funkcijos bus pridėtos greitai"

## Logika

### Rolių hierarchija ir rodymо sąlygos:

```php
// 1. Supervisor (visada rodomas supervisor meniu)
if ($user->isSupervisor()) {
    @include('partials.sidebar-supervisor')
}

// 2. School Admin (rodomas jei pasirinkta mokykla IR vartotojas yra tos mokyklos admin)
if ($currentSchool && ($user->isSupervisor() || $user->isSchoolAdmin($currentSchool->id))) {
    @include('partials.sidebar-school-admin')
}

// 3. Teacher (tik jei NE supervisor ir NE school admin)
if ($user->isTeacher() && !$user->isSupervisor() && !$user->isSchoolAdmin()) {
    @include('partials.sidebar-teacher')
}

// 4. Student (tik jei NE supervisor ir NE school admin)
if ($user->isStudent() && !$user->isSupervisor() && !$user->isSchoolAdmin()) {
    @include('partials.sidebar-student')
}

// 5. No role (jei neturi jokios rolės)
if (neturi jokios rolės) {
    Rodo įspėjimą "Prieiga nesuteikta"
}
```

### Kombinuoti roлей atvejai:

**Supervisor + School Admin:**
- Rodo SUPERVISOR sekciją
- Rodo HR skyriklis (`<hr>`)
- Rodo SCHOOL ADMIN sekciją

**School Admin (tik):**
- Rodo tik SCHOOL ADMIN sekciją

**Teacher/Student (tik):**
- Rodo tik atitinkamą sekciją su placeholder pranešimu

## CSS Stiliai

Pridėti nauji stiliai `layouts/admin.blade.php`:

```css
.sidebar-divider {
    border: 0;
    border-top: 1px solid #e0e0e0;
    margin: 1rem 1.5rem;
}

.admin-sidebar .nav-link.disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.admin-sidebar .alert {
    font-size: 0.85rem;
}
```

## Active Link Highlighting

Kiekvienas sidebar komponentas turi **active** klasės priskyrimą:

```php
// Pvz., supervisor sidebar
<a href="{{ route('schools.index') }}" 
   class="nav-link {{ request()->routeIs('schools.index') ? 'active' : '' }}">
    <i class="bi bi-building"></i> Mokyklos
</a>

// Pvz., school admin sidebar  
<a href="{{ route('schools.timetables.index', $currentSchool) }}" 
   class="nav-link {{ request()->routeIs('schools.timetables.*') ? 'active' : '' }}">
    <i class="bi bi-calendar3"></i> Tvarkaraščiai
</a>
```

## Naudojami Route Pattern'ai:

- `request()->routeIs('schools.index')` - tikslus route
- `request()->routeIs('schools.timetables.*')` - bet koks timetables route
- `request()->routeIs('users.*')` - bet koks users route

## Vartotojo Patirtis

### Supervisor:
1. Prisijungia kaip supervisor
2. Mato **Administratorius** sekciją su Mokyklos ir Vartotojai
3. Pasirenka mokyklą
4. **Automatiškai** matosi **{Mokyklos pavadinimas}** sekcija žemiau

### School Admin:
1. Prisijungia kaip mokyklos admin
2. Mato tik **{Mokyklos pavadinimas}** sekciją
3. Turi prieigą prie visų mokyklos valdymo funkcijų

### Teacher (ateityje):
1. Prisijungia kaip mokytojas
2. Mato **Mokytojas** sekciją
3. Kol kas funkcijos disabled su pranešimu

### Student (ateityje):
1. Prisijungia kaip mokinys
2. Mato **Mokinys** sekciją
3. Kol kas funkcijos disabled su pranešimu

## Ateities plėtra

Kai kuriamos mokytojo/mokinio funkcijos:

### Mokytojui:
1. **sidebar-teacher.blade.php** - pakeisti disabled į veikiančius route'us:
```php
<a href="{{ route('teacher.timetable') }}" class="nav-link">
    <i class="bi bi-calendar-week"></i> Mano tvarkaraštis
</a>
```

2. Pašalinti info alert
3. Pridėti papildomų funkcijų pagal poreikį

### Mokiniui:
1. **sidebar-student.blade.php** - pakeisti disabled į veikiančius route'us:
```php
<a href="{{ route('student.timetable') }}" class="nav-link">
    <i class="bi bi-calendar-check"></i> Mano tvarkaraštis
</a>
```

2. Pašalinti info alert
3. Pridėti papildomų funkcijų (pvz., pažymių langas)

## Privalumai

✅ **Moduliškumas** - kiekvienas sidebar komponentas atskirame faile  
✅ **Lengva prižiūrėti** - kiekviena rolė turi savo failą  
✅ **Lengva plėsti** - pridėti naują rolę = sukurti naują failą  
✅ **Active highlighting** - aiškiai matosi, kuriame puslapyje esame  
✅ **Rolių kombinacijos** - supervisor gali matyti ir mokyklos meniu  
✅ **User-friendly** - mokytojai/mokiniai mato, kad funkcijos bus greitai  
✅ **Saugumas** - tikrinama rolė backend pusėje  

## Saugumas

- Kiekvienas route vis tiek turi middleware autentifikaciją
- Sidebar tik slepia meniu, bet ne kontroliuoja prieigą
- Controller'iuose vyksta tikroji autorizacija
