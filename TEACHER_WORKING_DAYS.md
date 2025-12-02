# Mokytojų Darbo Dienų Funkcionalumas

## Aprašymas

Ši funkcija leidžia mokyklos administratoriui nustatyti, kuriomis savaitės dienomis (pirmadienis-penktadienis) kiekvienas mokytojas dirba konkrečiame tvarkaraštyje. Tai individualus nustatymas kiekvienam tvarkaraščiui.

## Funkcionalumas

### Galimybės:
- ✅ Nustatyti mokytojo darbo dienas individualiai kiekvienam tvarkaraščiui
- ✅ Tvarkaraščio generatorius automatiškai atsižvelgia į mokytojo darbo dienas
- ✅ Jei nepažymėta nė viena diena, laikoma, kad mokytojas dirba visas dienas (numatytoji elgsena)
- ✅ Patogi sąsaja mokytojų darbo dienų valdymui

### Kaip naudotis:

1. **Atidaryti tvarkaraštį**
   - Eikite į Mokykla → Tvarkaraščiai
   - Pasirinkite norimą tvarkaraštį

2. **Valdyti mokytojų darbo dienas**
   - Tvarkaraščio puslapyje spauskite mygtuką "Valdyti" sekcijoje "Mokytojų darbo dienos"
   - Pasirodys visų šiam tvarkaraščiui priskirtų mokytojų sąrašas
   - Kiekvieno mokytojo šalia matosi, kuriomis dienomis jis dirba

3. **Redaguoti mokytojo darbo dienas**
   - Spauskite "Redaguoti" prie mokytojo vardo
   - Pažymėkite dienas, kuriomis mokytojas dirba
   - Išsaugokite pakeitimus

4. **Generuoti tvarkaraštį**
   - Generuojant tvarkaraštį, sistema automatiškai atsižvelgs į mokytojų darbo dienas
   - Jei mokytojas nedirba tam tikrą dieną, tos dienos pamokos jam nebus priskiriamos
   - Nepavykus priskirti pamokų dėl mokytojo darbo dienų apribojimų, tai bus parodyta generavimo ataskaitoje su priežastimi "Mokytojas nedirba tą dieną"

## Techninė informacija

### Sukurti failai:

1. **Migracija**: `database/migrations/2025_12_02_000000_create_timetable_teacher_working_days_table.php`
   - Sukuria `timetable_teacher_working_days` lentelę
   - Stulpeliai: `timetable_id`, `teacher_login_key_id`, `day_of_week` (1-5)

2. **Modelis**: `app/Models/TimetableTeacherWorkingDay.php`
   - Eloquent modelis darbo dienų valdymui
   - Ryšiai su `Timetable` ir `LoginKey` (mokytojas)

3. **Atnaujinti modeliai**:
   - `app/Models/Timetable.php` - pridėti metodai:
     - `teacherWorkingDays()` - ryšys su darbo dienomis
     - `getTeacherWorkingDays($teacherId)` - gauti mokytojo darbo dienas
     - `isTeacherWorkingOnDay($teacherId, $dayOfWeek)` - patikrinti ar mokytojas dirba tą dieną

4. **Controller metodai** (`app/Http/Controllers/TimetableController.php`):
   - `getTeacherWorkingDays()` - gauti vieno mokytojo darbo dienas
   - `updateTeacherWorkingDays()` - atnaujinti mokytojo darbo dienas
   - `allTeachersWorkingDays()` - gauti visų mokytojų darbo dienas tvarkaraštyje

5. **Routes** (`routes/web.php`):
   - `GET /admin/schools/{school}/timetables/{timetable}/teacher-working-days`
   - `POST /admin/schools/{school}/timetables/{timetable}/teacher-working-days`
   - `GET /admin/schools/{school}/timetables/{timetable}/all-teachers-working-days`

6. **Generatorius** (`app/Services/TimetableGenerator.php`):
   - Patikrina ar mokytojas dirba tą dieną prieš priskirdamas pamoką
   - Priežasties kodas: `teacher_not_working`
   - Lietuviškas pavadinimas: "Mokytojas nedirba tą dieną"

7. **UI** (`resources/views/admin/timetables/show.blade.php`):
   - Nauja sekcija "Mokytojų darbo dienos"
   - Modal'as mokytojo darbo dienų redagavimui
   - AJAX užklausos duomenų įkėlimui ir išsaugojimui

### Dienų numeracija:
- 1 = Pirmadienis
- 2 = Antradienis
- 3 = Trečiadienis
- 4 = Ketvirtadienis
- 5 = Penktadienis

### Numatytoji elgsena:
Jei mokytojui nepriskirta nė viena darbo diena (lentelėje nėra įrašų), sistema laiko, kad mokytojas dirba **visas dienas**. Tai užtikrina, kad senųjų tvarkaraščių elgsena nepasikeitų ir nauji tvarkaraščiai veiktų be papildomų nustatymų.

## Pavyzdžiai

### Scenarijus 1: Mokytojas dirba tik pirmadienis, trečiadienis, penktadienis
- Pažymėkite tik šias dienas mokytojo darbo dienų lange
- Generuojant tvarkaraštį, mokytojas gaus pamokas tik šiomis dienomis
- Antradienio ir ketvirtadienio pamokos bus priskirtos kitiems mokytojams arba liks nepriskirtos

### Scenarijus 2: Mokytojas dirba visas dienas
- Galite pažymėti visas penkias dienas ARBA
- Palikti be pažymėjimų (numatytoji elgsena)

### Scenarijus 3: Skirtingi mokytojai skirtinguose tvarkaraščiuose
- Tas pats mokytojas gali turėti skirtingas darbo dienas skirtinguose tvarkaraščiuose
- Pavyzdžiui, tvarkaraštyje "2024 Ruduo" dirba Pir-Ket, o tvarkaraštyje "2024 Pavasaris" - Pir-Penk

## Testavimas

Rekomenduojami testai:
1. Sukurti naują tvarkaraštį su keliais mokytojais
2. Nustatyti vienam mokytojui darbo dienas (pvz., tik Pir-Tre)
3. Generuoti tvarkaraštį
4. Patikrinti, kad mokytojas negavo pamokų nedarbo dienomis
5. Peržiūrėti generavimo ataskaitą - turėtų būti matomas priežasties kodas "Mokytojas nedirba tą dieną"
