<?php

namespace App\Http\Controllers\Api\Bitrix;

use App\Http\Controllers\Controller;
use App\Models\Admin\UserPersonalInfo;
use App\Models\BitrixHashes;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class PersonController extends Controller
{
    /**
     * @OA\Put(
     *     path="/api/main/api-1c/v1/persons/create_update_list",
     *     summary="Создание или обновление физических лиц",
     *     description="Создает или обновляет список физических лиц по Bitrix ID",
     *     tags={"Bitrix"},
     *     security={{"sanctum":{}}},
     *
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"persons"},
     *             @OA\Property(
     *                 property="persons",
     *                 type="array",
     *                 @OA\Items(
     *                     required={
     *                         "ID","eMail","insuranceNumber","identityDocument",
     *                         "birthDate","gender","mobilePhoneNumber",
     *                         "firstName","lastName"
     *                     },
     *                     @OA\Property(property="ID", type="string", example="BITRIX_P_001"),
     *                     @OA\Property(property="eMail", type="string", format="email", example="test@mail.com"),
     *                     @OA\Property(property="insuranceNumber", type="string", example="12345678901234"),
     *
     *                     @OA\Property(
     *                         property="identityDocument",
     *                         type="object",
     *                         required={"serias","number"},
     *                         @OA\Property(property="serias", type="string", example="AB"),
     *                         @OA\Property(property="number", type="string", example="1234567")
     *                     ),
     *
     *                     @OA\Property(property="birthDate", type="string", example="25-12-1995"),
     *                     @OA\Property(property="gender", type="string", enum={"male","female"}),
     *                     @OA\Property(property="mobilePhoneNumber", type="string", example="+998901234567"),
     *                     @OA\Property(property="firstName", type="string", example="Ali"),
     *                     @OA\Property(property="lastName", type="string", example="Valiyev"),
     *                     @OA\Property(property="patronymic", type="string", nullable=true, example="Akmalovich")
     *                 )
     *             )
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=201,
     *         description="Список лиц успешно обработан",
     *         @OA\JsonContent(
     *             @OA\Property(property="message", type="string"),
     *             @OA\Property(property="processed_count", type="integer")
     *         )
     *     ),
     *
     *     @OA\Response(
     *         response=422,
     *         description="Ошибка валидации"
     *     ),
     *
     *     @OA\Response(
     *         response=500,
     *         description="Внутренняя ошибка сервера"
     *     )
     * )
     */

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // Validate input
            $validated = $request->validate([
                'persons' => 'required|array',
                'persons.*.ID' => 'required|string',
                'persons.*.eMail' => 'required|email|max:255',
                'persons.*.insuranceNumber' => 'required|string',
                'persons.*.identityDocument.serias' => 'required|string',
                'persons.*.identityDocument.number' => 'required|string',
                'persons.*.birthDate' => 'required|date_format:d-m-Y',
                'persons.*.gender' => 'required|in:male,female',
                'persons.*.mobilePhoneNumber' => 'required|string',
                'persons.*.firstName' => 'required|string|max:255',
                'persons.*.lastName' => 'required|string|max:255',
                'persons.*.patronymic' => 'nullable|string|max:255',
            ]);

            DB::beginTransaction();

            $personsArray = $request->persons;

            Log::info('Processing persons store request', [
                'person_count' => count($personsArray)
            ]);

            if (empty($personsArray)) {
                throw new Exception('No persons provided in request');
            }

            foreach ($personsArray as $person) {
                //print_r($person); die;
                Log::info('Processing person', [
                    'person_id' => $person['ID'],
                    'email' => $person['eMail'],
                    'first_name' => $person['firstName']
                ]);

                $bitrixHash = BitrixHashes::where('type', 'P')->where('hash', $person['ID'])->first();

                if ($bitrixHash) {
                    // Update existing person
                    $user = User::where('id', $bitrixHash->local_id)->first();
                    if (!$user) {
                        Log::error('User not found for existing Bitrix hash', [
                            'bitrix_hash_id' => $bitrixHash->id,
                            'person_id' => $person['ID']
                        ]);
                        throw new Exception("User not found for person ID {$person['ID']}");
                    }

                    $user->update([
                        'username' => $person['eMail'],
                        'email' => $person['eMail'],
                    ]);

                    $userPersonalInfo = UserPersonalInfo::where('user_id', $bitrixHash->local_id)->first();
                    if (!$userPersonalInfo) {
                        Log::error('User personal info not found', [
                            'user_id' => $bitrixHash->local_id,
                            'person_id' => $person['ID']
                        ]);
                        throw new Exception("User personal info not found for person ID {$person['ID']}");
                    }

                    $userPersonalInfo->update([
                        'doc_pin' => $person['insuranceNumber'],
                        'doc_number' => $person['identityDocument']['serias'] . $person['identityDocument']['number'],
                        'doc_type' => 2,
                        'birthday' => Carbon::createFromFormat('d-m-Y', $person['birthDate'])->format('Y-m-d'),
                        'gender' => $person['gender'] === 'male' ? 1 : 2,
                        'phone' => $this->normalizePhone($person['mobilePhoneNumber']),
                        'f_name' => $person['firstName'],
                        'l_name' => $person['lastName'],
                        'm_name' => $person['patronymic'],
                        'lang_code' => 'uz',
                    ]);

                    Log::info('Person updated successfully', [
                        'person_id' => $person['ID'],
                        'user_id' => $user->id
                    ]);
                } else {
                    //print_r($person); die;
                    $checkUser = User::where('email', $person['eMail'])->first();
                    if (!$checkUser) {
                        // Create new person
                        $user = new User();
                        $user->username = $person['eMail'];
                        $user->email = $person['eMail'];
                        $user->user_hash = $this->uniqueId();
                        $user->password = Hash::make('123456'); // Note: Consider using a more secure default password strategy
                        $user->status = 2;
                        $user->save();

                        $bitrixHash = new BitrixHashes();
                        $bitrixHash->type = 'P';
                        $bitrixHash->hash = $person['ID'];
                        $bitrixHash->local_id = $user->id;
                        $bitrixHash->save();

                        $userPersonalInfo = new UserPersonalInfo();
                        $userPersonalInfo->user_id = $user->id;
                        $userPersonalInfo->is_emp = 2;
                        $userPersonalInfo->doc_pin = $person['insuranceNumber'];
                        $userPersonalInfo->doc_number = $person['identityDocument']['serias'] . $person['identityDocument']['number'];
                        $userPersonalInfo->doc_type = 2;
                        $userPersonalInfo->birthday = Carbon::createFromFormat('d-m-Y', $person['birthDate'])->format('Y-m-d');
                        $userPersonalInfo->gender = $person['gender'] === 'male' ? 1 : 2;
                        $userPersonalInfo->phone = $this->normalizePhone($person['mobilePhoneNumber']);
                        $userPersonalInfo->f_name = $person['firstName'];
                        $userPersonalInfo->l_name = $person['lastName'];
                        $userPersonalInfo->m_name = $person['patronymic'];
                        $userPersonalInfo->lang_code = 'uz';
                        $userPersonalInfo->doc_sign_type = 1;
                        $userPersonalInfo->sort = 1;
                        $userPersonalInfo->save();

                        Log::info('Person created successfully', [
                            'person_id' => $person['ID'],
                            'user_id' => $user->id,
                            'bitrix_hash_id' => $bitrixHash->id
                        ]);
                    } else {
                        return response()->json([
                            'message' => $person['eMail'].' - ushbu user tizimida mavjud!!!',
                        ], 201);
                    }
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Persons create/update successful',
                'processed_count' => count($personsArray)
            ], 201);

        } catch (ValidationException $e) {
            Log::error('Validation failed for persons store request', [
                'errors' => $e->errors(),
                'input' => $request->all()
            ]);
            return response()->json([
                'message' => 'Validation failed',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error processing persons store request', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json([
                'message' => 'Error processing request',
                'error' => $e->getMessage()
            ], 500);
        }
    }

   public function normalizePhone($phone)
    {
        //
        $digits = preg_replace('/\D/', '', $phone);

        // Oxirgi 9 ta raqamni olish
        return substr($digits, -9);
    }
}
